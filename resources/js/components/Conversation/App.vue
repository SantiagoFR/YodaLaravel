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
    <div class="row justify-content-center">
      <div class="col-9">
        <section ref="chatArea" class="chat-area">
        <p v-for="message in messages" class="message" :class="{ 'message-out': message.author === 'you', 'message-in': message.author !== 'you' }">
          {{ message.text }}
        </p>
      </section>
      </div>
    </div>
  </div>
</template>

<script>
//TODO: Change form to HTML
import { required, minLength } from 'vuelidate/lib/validators'

export default {
  components: {
  },
  mounted () {
      axios.get('getAuthorizationApi')
      .then(res => {
          console.log('Conversacion iniciada correctamente')
      }).catch(err => {
          console.log(err)
      })
  },
  data () {
    return {
      messages: [],
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
      this.messages.push({
        'text': this.form.text,
        'author': 'you'
      })
      axios.post('talk', this.form)
      .then(res => {
        this.messages.push({
          'text': res.data[0].messageList[0],
          'author': 'yoda'
        })
      }).catch(err => {
          console.log(err)
      })
      
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
