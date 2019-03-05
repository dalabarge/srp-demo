'use strict'

import _ from 'lodash'
import moment from 'moment'
import randomStrings from 'random-strings'
import {BigInteger} from 'jsbn'
import Config from './Config'

/**
 * Client-Side SRP-6a Implementation.
 *
 * @example srp = Client.configure(N = '21766174458...', g = '2', k = '5b9e8ef0...')
 *     verifier = srp.enroll(I = 'user123', p = 'password')
 *            A = srp.challenge(I = 'user123', p = 'password')
 *           M2 = srp.response(B = '48147d013e3a2...', s = '21d1546a18f9...')
 *       result = srp.confirm(M2 = '937ee2752d2d0a18eea2e7')
 */
export default class Client {

    /**
     * Inject the dependencies for the SRP service.
     *
     * @param \SRP.Config config
     */
    constructor(config) {
        this._config = config
        this.ONE = new BigInteger('1')
        this.ZERO = new BigInteger('0')
        this.password = null
        this.salt = null
        this.identity = null
        this.private = null
        this.public = null
        this.session = null
        this.proof = null
    }

    /**
     * Configure the SRP shared settings.
     *
     * @param string prime     configured value N as a decimal
     * @param string generator configured value g as a decimal
     * @param string key       configured value k as a hexidecimal
     * @param string algorithm name (e.g.: sha256)
     *
     * @return \SRP.Client
     */
    static configure(prime, generator, key, algorithm) {
        return new Client(new Config(prime, generator, key, algorithm))
    }

    /**
     * Step 0: Generate a new verifier v for the user identity I and password P with salt s.
     *
     * @param string identity I of user
     * @param string password P for user
     * @param string salt     value s chosen at random
     *
     * @return string
     */
    enroll(identity, password, salt) {
        this.identity = identity
        this.salt = salt

        const signature = this.signature(identity, password, this.salt)

        return this._toHex(this._config.generator().modPow(signature, this._config.prime()))
    }

    /**
     * Step 1: Generates a one-time client key A encoded as a hexadecimal.
     *
     * @param string identity I of user
     * @param string password P for user
     * @param string salt     hexadecimal value s for user's password P
     *
     * @return string
     */
    identify(identity, password, salt) {
        this.identity = identity
        this.password = password
        this.salt = salt

        while (!this.public || this.public.mod(this._config.prime()).equals(this.ZERO)) {
            this.private = this.number()
            this.public = this._config.generator().modPow(this.private, this._config.prime())
        }

        return this._unpad(this._toHex(this.public))
    }

    /**
     * Step 2: Create challenge response to server's public key challenge B with a proof of password M1.
     *
     * @param string server hexadecimal key B from server
     * @param string salt   value s for user's public value A
     *
     * @throws Error for invalid public key B
     *
     * @return string
     */
    challenge(server, salt) {
        // Verify valid public key
        server = this._fromHex(this._unpad(server))
        if (server.mod(this._config.prime()).equals(this.ZERO)) {
            throw 'Server public key failed B mod N == 0 check.'
        }

        // Create proof M1 of password using A and previously stored verifier v
        let union = this._fromHex(this.hash(this._toHex(this.public) + this._toHex(server)))
        const signature = this.signature(this.identity, this.password, salt)
        const exponent = union.multiply(signature).add(this.private)
        const shared = this._unpad(this._toHex(server.subtract(this._config.generator().modPow(signature, this._config.prime()).multiply(this._config.key())).modPow(exponent, this._config.prime())))

        // Compute verification M = H(A | B | S)
        const message = this._unpad(this.hash(this._toHex(this.public) + this._toHex(server) + shared))

        // Generate proof of password M1 = H(A | M | S) using client public key A and shared key S
        this.proof = this._unpad(this.hash(this._toHex(this.public) + message + shared))

        // Clear stored state for P, s, a, and A
        this.password = null
        this.salt = null
        this.private = null
        this.public = null

        // Save shared session key K and
        this.session = this.hash(shared)

        // Respond with message M1
        return message
    }

    /**
     * Step 3: Confirm server's proof of shared key message M2 against
     * client's proof of password M1.
     *
     * @param string proof of shared key M2 from server
     *
     * @return bool
     */
    confirm(proof) {
        return this.proof &&
            this.proof === proof
    }

    /**
     * Compute the RFC 2945 signature X from x = H(s | H(I | ":" | P)).
     *
     * @param string identity I of user
     * @param string password P for user
     * @param string salt     value s chosen at random
     *
     * @return \jsbn.BigInteger
     */
    signature(identity, password, salt) {
        this._assertNotEmpty(arguments)

        const hash = this._unpad(this.hash(identity + ':' + password))

        return this._fromHex(this._unpad(this.hash((salt + hash).toUpperCase())))
            .mod(this._config.prime())
    }

    /**
     * Get the user's identity I.
     *
     * @return string
     */
    identity() {
        return this.identity
    }

    /**
     * Get shared session key K = H(S).
     *
     * @return string
     */
    session() {
        return this.session
    }

    /**
     * Generate a new random salt.
     *
     * @param string identity
     * @param string salt
     *
     * @return string
     */
    salt(identity, salt) {
        this.identity = identity
        this.salt = salt

        return this.hash(moment().seconds() + ':' + this.number())
    }

    /**
     * Generate an RFC 5054 compliant private key value (a or b) which is in the
     * range [1, N-1] of at least 256 bits.
     *
     * A nonce based on H(I|:|s|:|t) is added to ensure random number generation.
     *
     * @return \jsbn.BigInteger
     */
    number() {
        const bits = Math.max([256, this._toHex(this._config.prime()).length])

        let number = new BigInteger('0')

        while (number.equals(this.ZERO)) {
            number = this._bytes(1 + bits / 8)
                .add(this._nonce(this.identity, this.salt))
                .modPow(this.ONE, this._config.prime())
        }

        return number
    }

    /**
     * Hash key derivative function x using H algorithm.
     *
     * @param string value
     *
     * @return string
     */
    hash(value) {
        return this._config.algorithm(value)
    }

    /**
     * Generate a new nonce H(I|:|s|:|t) string based on the user's identity I, salt s, and t time.
     *
     * @param string identity
     * @param string salt
     *
     * @return \jsbn.BigInteger
     */
    _nonce(identity, salt) {
        return this._fromHex(this.hash(identity + ':' + salt + ':' + moment().valueOf()))
    }

    /**
     * Strip leading zeros off hexadecimal value.
     *
     * @param string hexadecimal
     *
     * @return string
     */
    _unpad(hexadecimal) {
        return _.trimStart(hexadecimal, '0')
    }

    /**
     * Generate random bytes as hexadecimal string.
     *
     * @param int bytes
     *
     * @return \jsbn.BigInteger
     */
    _bytes(bytes = 32) {
        return this._fromHex(randomStrings.hex(bytes))
    }

    /**
     * Assert that none of the params were emtpy strings when trimmed.
     *
     * @param string[] params
     *
     * @throws Error for an empty parameter.
     */
    _assertNotEmpty(params) {
        _.each(params, (param) => {
            if (_.isNil(_.trim(param, ''))) {
                throw 'An empty string was passed as parameter to signature.'
            }
        })
    }

    /**
     * Create a new BigInteger from a hexadecimal string.
     *
     * @param  string number as a hexadecimal
     *
     * @return \jsbn.BigInteger
     */
    _fromHex(number) {
        return new BigInteger('' + number, 16)
    }

    /**
     * Convert a BigInteger to a hexadecimal string.
     *
     * @param \jsbn.BigInteger
     *
     * @return string
     */
    _toHex(number) {
        return number.toString(16)
    }
}
