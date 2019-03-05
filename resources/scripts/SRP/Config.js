'use strict'

import {BigInteger} from 'jsbn'

export default class Client {

    /**
     * Create a shared SRP configuration.
     *
     * @param string prime       configured value N as a decimal
     * @param string generator   configured value g as a decimal
     * @param string key         configured value k as a hexidecimal
     * @param function algorithm (e.g.: sha256)
     *
     * @return \SRP.Client
     */
    constructor(prime, generator, key, algorithm)
    {
        this._prime = new BigInteger(prime, 10)
        this._generator = new BigInteger(generator, 10)
        this._key = new BigInteger(key, 16)
        this._algorithm = algorithm
    }

    /**
     * Get the large safe prime N for computing g^x mod N.
     *
     * @return \jsbn.BigInteger
     */
    prime() {
        return this._prime
    }

    /**
     * Get the configured generator g of the multiplicative group.
     *
     * @return \jsbn.BigInteger
     */
    generator() {
        return this._generator
    }

    /**
     * Get the derived key k = H(N, g).
     *
     * @return \jsbn.BigInteger
     */
    key() {
        return this._key
    }

    /**
     * Get the hashing algorithm name.
     *
     * @param string|null value
     *
     * @return string
     */
    algorithm(value) {
        return value ? this._algorithm(value).toString().toLowerCase() : this._algorithm
    }

    /**
     * Convert to something that can be JSON serialized.
     *
     * @return Object
     */
    jsonSerialize() {
        return this.toObject()
    }

    /**
     * Create a new config from an Object.
     *
     * @param Object config
     *
     * @return \SRP.Config
     */
    static fromObject(config) {
        return new Config(config.prime, config.generator, config.key, config.algorithm)
    }

    /**
     * Cast to an Object.
     *
     * @return Object
     */
    toObject() {
        return {
            prime: this._prime.toString(),
            generator: this._generator.toString(),
            key: this._key.toHex(),
            algorithm: this._algorithm,
        }
    }

    /**
     * Cast to JSON representation.
     *
     * @param int options for encoding
     *
     * @return string
     */
    toJson(options) {
        return JSON.stringify(this.toObject(), null, options)
    }
}
