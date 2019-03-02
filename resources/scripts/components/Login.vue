<template>
    <form :action="api" method="POST" v-on:submit.prevent="submit">
        <h3 class="uk-margin-remove-top">{{ title }}</h3>
        <p>Please login to your account:</p>
        <div v-if="has_errors" uk-alert class="uk-alert uk-alert-danger">
            <a class="uk-alert-close" uk-close></a>
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
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-envelope"></i></a>
                    <input class="uk-input" id="email" name="email" type="text" required v-model.lazy="form.email" v-on:blur="identify(form.email)" :disabled="response.challenge" :autofocus="! user || ! user.email" tabindex="1">
                </div>
            </div>
        </div>
        <div v-if="response.challenge" class="uk-margin-bottom">
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
                    <input class="uk-input" id="password" type="password" required v-model.lazy="password" v-on:blur="solve(form.email, password)" tabindex="2" :autofocus="user && user.email">
                </div>
            </div>
        </div>
        <div class="uk-flex uk-flex-middle uk-flex-between">
            <button class="uk-button uk-button-primary uk-margin-small-right" type="submit" tabindex="3" :disabled="this.loading || (response.challenge && (! password || ! form.key || ! form.proof))">Sign In</button>
            <a class="uk-button uk-button-link" :href="register" tabindex="4">Create an Account &rarr;</a>
        </div>
    </form>
</template>
<script>
    import Api from '../utilities/Api'
    export default {
        props: ['api', 'redirect', 'register', 'user', 'error', 'title'],
        data() {
            return {
                loading: false,
                errors: {
                    errors: {},
                    message: this.error,
                },
                response: {
                    identity: null,
                    challenge: null,
                    proof: null,
                },
                form: {
                    email: this.user && this.user.email ? this.user.email : null,
                    key: null,
                    proof: null,
                },
                password: null
            }
        },
        mounted() {
            if( ! this.error && this.user && this.user.email ) {
                this.identify(this.user.email)
            }
        },
        watch: {
            'form.email': {
                handler: function(email) {
                    this.identify(email)
                }
            },
            password(password) {
                this.solve(this.form.email, password)
            }
        },
        computed: {
            has_errors() {
                return !_.isEmpty(this.errors.message)
            },
            list_errors() {
                return _.flatten(_.values(this.errors.errors))
            },
        },
        methods: {
            identify(email) {
                if( this.loading || this.response.challenge ) {
                    return
                }

                this.load()

                Api.post(this.api, {email})
                    .then((response) => {
                        this.response.identity = response.data.identity
                        this.response.challenge = response.data.challenge
                        this.resetErrors()
                    })
                    .catch((error) => {
                        this.setErrors(error.data)
                    })
                    .then(() => {
                        this.loaded()
                    })
            },
            solve(email, password) {
                if( ! this.loading ) {
                    this.submit()
                }
            },
            submit() {
                if( this.loading || ! this.response.challenge || ! this.form.email || ! this.form.key || ! this.form.proof) {
                    if( ! this.password ) {
                        return this.identify(this.form.email)
                    }
                    return
                }

                this.load()

                Api.patch(this.api, this.form)
                    .then((response) => {
                        this.response.identity = response.data.identity
                        this.response.proof = response.data.proof
                        this.resetErrors()
                    })
                    .catch((error) => {
                        this.setErrors(error.data)
                    })
                    .then(() => {
                        this.loaded()
                    })
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
            load() {
                this.loading = true
            },
            loaded() {
                this.loading = false
            }
        }
    }
</script>
