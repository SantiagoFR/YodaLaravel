<template>
  <div>
    <b-form @submit.prevent="onSubmit">
      <b-row class="justify-content-center">
        <b-col cols="7">
          <b-form-group>
            <b-form-input 
            v-model="form.text" 
            placeholder="Introduzca el texto"
            :state="validateState('text')"
            aria-describedby="input-1-live-feedback"></b-form-input>
            <b-form-invalid-feedback id="input-1-live-feedback">
              This is a required field and must be at least 3 characters.
            </b-form-invalid-feedback>
          </b-form-group>
        </b-col>
        <b-col cols="2">
          <b-button variant="outline-secondary" style="width:100px" type="submit">Enviar</b-button>
        </b-col>
      </b-row>
    </b-form>
  </div>
</template>

<script>
import { required, minLength } from 'vuelidate/lib/validators'

export default {
  components: {
  },
  mounted () {
      axios.get('api/getAuthorizationApi')
      .then(res => {
          this.chatUrl = res.data
          console.log('Conversacion iniciada correctamente')
      }).catch(err => {
          console.log(err)
      })
  },
  data () {
    return {
      chatUrl: '',
      form: {
        text: null
      }
    }    
  },
  validations: {
    form: {
      text: {
        required,
        minLength: minLength(4)
      }
    }
  },
  methods: {
    onSubmit: function () {
      this.$v.form.$touch();
      if (this.$v.form.$anyError) {
        return;
      }
      
      console.log(this.form.text)
      this.form.text=''
    },
    validateState(name) {
      const { $dirty, $error } = this.$v.form[name];
      return $dirty ? !$error : null;
    },
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
