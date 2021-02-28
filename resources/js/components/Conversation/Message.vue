<template>
    <li>
        <div :class="message.user==='user' ? 'rightside-right-chat' : 'rightside-left-chat'">
            <span><i class="fa fa-circle" aria-hidden="true"></i>{{message.user==='user' ? 'You' : 'Yoda'}} - <small>{{new Date(message.datetime)}}</small> </span><br><br>
            <p v-html="message.messageList[0]"></p>
        </div>
    </li>
</template>
<script>
    export default {
        props: ['message']
    }
</script>