<template>
  <div>  
    <div class="container main-section">
      <div class="row">
        <div class="col-12 right-sidebar">
          <div class="row">
            <div class="col-md-12 right-header">
              <div class="right-header-img">
                <img src="img/mini-yoda.jpg">
              </div>
              <div class="right-header-detail">
                <p>Yoda</p>
                <span v-if="writing"> writing... </span>
                <span v-else>already {{messages.length}} messages </span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 right-header-contentChat">
              <ul>
                <Message v-for="(message, i) in messages" :message="message" :key="i"> </Message>
              </ul>
            </div>
          </div>
          
          <b-form @submit.prevent="onSubmit">
            <div class="row">
                <div class="col-md-12 right-chat-textbox">
                    <b-form-group>
                      <b-input-group class="mt-3">                        
                        <b-form-input 
                        autocomplete="off"
                        v-model="form.text" 
                        placeholder="Write to Yoda"
                        :state="validateState('text')"
                        aria-describedby="input-1-live-feedback"></b-form-input>
                        <b-form-invalid-feedback id="input-1-live-feedback">
                          This is a required field and must be at least 3 characters.
                        </b-form-invalid-feedback>
                        <b-input-group-append>
                          <b-button type="submit" variant="outline-success"> <b-icon icon="cursor-fill"></b-icon> </b-button>
                        </b-input-group-append>
                      </b-input-group>
                    </b-form-group>
                </div>
            </div>          
          </b-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { required, minLength } from 'vuelidate/lib/validators'
import Message from './Message.vue'

export default {
  components: {
    Message
  },
  created () {
      axios.get('getHistory')
      .then(res => {
          if (res.data !== "error") this.messages = res.data
      }).catch(err => {
          console.log(err)
      })
      $(document).ready(function(){
        var height = $(window).height();
        $('.left-chat').css('height', (height - 92) + 'px');
        $('.right-header-contentChat').css('height', (height - 163) + 'px');
      });
  },
  data () {
    return {
      messages: [],
      form: {
        text: null
      },
      writing: false
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
  watch: {
    messages: async function () {
      await new Promise(r => setTimeout(r, 250));
      $('.right-header-contentChat').scrollTop($('.right-header-contentChat')[0].scrollHeight);
    }
  },
  methods: {
    onSubmit: function () {
      this.$v.form.$touch();
      if (this.$v.form.$anyError) {
        return;
      }
      this.messages.push({
        'messageList': [this.form.text],
        'user': 'user',
        'datetime': Date.now()
      })
      this.writing=true
      axios.post('talk', this.form)
      .then(res => {
        if (res.data.type === "message") this.messages.push({'messageList': res.data.message.messageList, 'user': 'bot', 'datetime': Date.now()})
        else if (res.data.type === "list") this.messages.push({'messageList': [res.data.message], 'user': 'bot', 'datetime': Date.now()})
        this.writing=false
      }).catch(err => {
          console.log(err)
      })
      
      this.form.text=''
      this.$v.$reset();

    },
    validateState(name) {
      const { $dirty, $error } = this.$v.form[name];
      return $dirty ? !$error : null;
    },
  }
}
</script>
