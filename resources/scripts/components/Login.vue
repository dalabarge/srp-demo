<template>
    <form :action="api" method="POST" v-on:submit.prevent="submit">
        <h3 class="uk-margin-remove-top">{{ title }}</h3>
        <p>Please login to your account:</p>
        <div v-if="has_errors" uk-alert class="uk-alert uk-alert-danger">
            <p v-html="errors.message"></p>
            <ul v-if="list_errors.length >= 1" class="uk-list">
                <li v-for="error in list_errors" v-html="error"></li>
            </ul>
        </div>
        <div class="uk-margin-bottom">
            <label class="uk-form-label uk-form-required" for="email">
                Email
            </label>
            <div class="uk-form-controls">
                <div class="uk-inline uk-width-1-1" v-on:click="activate">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-envelope"></i></a>
                    <input class="uk-input" id="email" name="email" type="email" required v-model="email" :disabled="disable_email" :autofocus="! user || ! user.email" tabindex="1" :style="disable_email ? {cursor: 'pointer'} : {cursor: 'default'}">
                </div>
            </div>
        </div>
        <div v-if="show_password" class="uk-margin-bottom">
            <div class="uk-flex uk-flex-middle uk-flex-between">
                <label class="uk-form-label uk-form-required" for="password">
                    Password
                </label>
                <div class="uk-text-right">
                    <a href="#" class="uk-button uk-button-link" tabindex="5">Forgot password?</a>
                </div>
            </div>
            <div class="uk-form-controls uk-margin-small-bottom">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-lock"></i></a>
                    <input class="uk-input" id="password" type="password" required v-model="password" tabindex="2" :autofocus="user && user.email" :disabled="disable_password">
                </div>
            </div>
        </div>
        <div class="uk-flex uk-flex-middle uk-flex-between">
            <button class="uk-button uk-button-primary uk-margin-small-right" type="submit" tabindex="3" :disabled="disable_button" v-html="button_text"></button>
            <a class="uk-button uk-button-link" :href="register" tabindex="4">Create an Account &rarr;</a>
        </div>
    </form>
</template>
<script>
    import Api from '../libs/Api'
    import Client from '../SRP/Client'
    import crypto from 'crypto-js/core'
    import SHA256 from 'crypto-js/sha256'
    export default {
        props: ['api', 'redirect', 'register', 'user', 'error', 'title', 'config'],
        data() {
            return {
                step: 1,
                loading: false,
                client: null,
                email: this.user && this.user.email ? this.user.email : '',
                password: '',
                server: {
                    salt: null,
                    key: null,
                },
                errors: {
                    errors: {},
                    message: this.error,
                },
            }
        },
        mounted() {
            this.reset({
                errors: {},
                message: null,
            })
            if( ! this.error && this.user && this.user.email ) {
                this.initiate(this.user.email)
            }
        },
        computed: {
            has_errors() {
                return !_.isEmpty(this.errors.message)
            },
            list_errors() {
                return _.flatten(_.values(this.errors.errors))
            },
            show_password() {
                return this.step === 2
            },
            disable_email() {
                return this.loading
                    || this.step > 1
            },
            disable_password() {
                return this.loading
                    || this.step > 2
            },
            disable_button() {
                return _.isEmpty(this.email)
                    || this.disable_password
                    || (this.step === 2 && _.isEmpty(this.password))
            },
            button_text() {
                if( this.step < 2 ) {
                    return this.loading ? 'Loading...' : 'Continue &rarr;'
                }

                return this.loading ? 'Authenticating...' : 'Sign In'
            }
        },
        methods: {
            load() {
                this.loading = true
            },
            loaded() {
                this.loading = false
            },
            setErrors(data) {
                this.errors = data
            },
            resetErrors() {
                this.setErrors({
                    errors: {},
                    message: null,
                })
            },
            submit() {
                if( this.loading ) {
                    return
                }

                this.load()

                this.resetErrors()

                if( this.step === 1 ) {
                    return this.initiate(this.email)
                }

                if( this.step === 2 ) {
                    return this.identify(this.email, this.password)
                }

                this.loaded()
            },
            initiate(email) {
                if( this.step !== 1 || _.isEmpty(email)) {
                    return
                }

                Api.post(this.api, {email})
                    .then((response) => {
                        this.server.salt = response.data.salt
                        this.server.key = response.data.key
                        this.step = 2
                        this.loaded()
                    })
                    .catch((error) => {
                        if( error.status === 401 ) {
                            return this.initiate(email)
                        }
                        this.reset(error.data)
                    })
            },
            identify(email, password) {
                if( this.step !== 2 || _.isEmpty(email) || _.isEmpty(password) ) {
                    return
                }

                let key = this.client.identify(email, password, this.server.salt)
                let proof = this.client.challenge(this.server.key, this.server.salt)

                Api.patch(this.api, {email, key, proof})
                    .then((response) => {
                        this.step = 3
                        this.confirm(response.data.proof)
                    })
                    .catch((error) => {
                        if( error.status === 401 ) {
                            this.setp = 2
                            this.password = ''
                            this.setErrors(error.data)
                            this.loaded()
                            return this.initiate(email)
                        }

                        this.reset(error.data)
                    })
            },
            confirm(proof) {
                if( ! this.client.confirm(proof) ) {
                    return this.reset({
                        errors: {},
                        message: 'The server\'s proof of shared secret key does not match.'
                    })
                }

                document.location = this.redirect
            },
            activate() {
                if( this.disable_email ) {
                    this.reset()
                }
            },
            reset(errors, step) {
                this.step = step || 1
                this.password = ''
                this.server.salt = null
                this.server.key = null
                this.client = Client.configure(
                    this.config.prime,
                    this.config.generator,
                    this.config.key,
                    SHA256
                )
                if( errors ) {
                    this.setErrors(errors)
                }
                this.loaded()
            }
        }
    }
</script>
