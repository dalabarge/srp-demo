<template>
    <form ref="form" :action="api" method="POST" v-on:submit.prevent="submit">
        <input type="hidden" name="_token" :value="csrf" />
        <input type="hidden" name="verifier" :value="verifier" />
        <input type="hidden" name="salt" :value="salt" />
        <h3 class="uk-margin-remove-top">{{ title }}</h3>
        <div v-if="has_errors" uk-alert class="uk-alert uk-alert-danger">
            <p v-if="errors.message" v-html="errors.message"></p>
            <ul v-if="list_errors.length >= 1" class="uk-list">
                <li v-for="error in list_errors" v-html="error"></li>
            </ul>
        </div>
        <div class="uk-margin-bottom">
            <label class="uk-form-label uk-form-required" for="name">
                Name
            </label>
            <div class="uk-form-controls">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-envelope"></i></a>
                    <input class="uk-input" id="name" name="name" type="text" required v-model="name" :autofocus="! user || (user && ! user.name)" tabindex="1">
                </div>
            </div>
        </div>
        <div class="uk-margin-bottom">
            <label class="uk-form-label uk-form-required" for="email">
                Email
            </label>
            <div class="uk-form-controls">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-envelope"></i></a>
                    <input class="uk-input" id="email" name="email" type="email" required v-model="email" :autofocus="user && user.name && ! user.email" tabindex="2">
                </div>
            </div>
        </div>
        <div class="uk-margin-bottom">
            <label class="uk-form-label uk-form-required" for="password">
                Password
            </label>
            <div class="uk-form-controls uk-margin-small-bottom">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-lock"></i></a>
                    <input class="uk-input" id="password" type="password" required v-model="password" tabindex="3" :autofocus="user && user.name && user.email">
                </div>
            </div>
        </div>
        <div class="uk-flex uk-flex-middle uk-flex-between">
            <button class="uk-button uk-button-primary uk-margin-small-right" type="submit" tabindex="4" :disabled="disable_button" v-html="button_text"></button>
            <a class="uk-button uk-button-link" :href="login" tabindex="5">Back to Login &rarr;</a>
        </div>
    </form>
</template>
<script>
    import Client from '../SRP/Client'
    import randomStrings from 'random-strings'
    import crypto from 'crypto-js/core'
    import SHA256 from 'crypto-js/sha256'
    export default {
        props: ['api', 'login', 'user', 'bag', 'title', 'config'],
        data() {
            return {
                loading: false,
                client: null,
                name: this.user && this.user.name ? this.user.name : '',
                email: this.user && this.user.email ? this.user.email : '',
                verifier: null,
                salt: null,
                password: '',
                errors: {
                    errors: this.bag || {},
                    message: null,
                },
            }
        },
        mounted() {
            this.client = Client.configure(
                this.config.prime,
                this.config.generator,
                this.config.key,
                SHA256
            )
        },
        computed: {
            csrf() {
                const token = document.head.querySelector('meta[name="csrf-token"]')
                return token.content || ''
            },
            has_errors() {
                return !_.isEmpty(this.list_errors)
                    || !_.isEmpty(this.errors.message)
            },
            list_errors() {
                return _.flatten(_.values(this.errors.errors))
            },
            disable_button() {
                return _.isEmpty(this.name)
                    || _.isEmpty(this.email)
                    || _.isEmpty(this.password)
            },
            button_text() {
                return this.loading ? 'Registering...' : 'Create Account'
            }
        },
        methods: {
            load() {
                this.loading = true
            },
            submit() {
                if( this.loading ) {
                    return
                }

                this.load()

                this.errors = {
                    errors: {},
                    message: null,
                }

                this.salt = this.client.salt(this.email, randomStrings.hex(32))
                this.verifier = this.client.enroll(this.email, this.password, this.salt)

                this.password = ''

                _.delay(() => { this.$refs.form.submit() }, 250)
            },
        }
    }
</script>
