<template>
    <li>
        <div :class="message.author==='you' ? 'rightside-right-chat' : 'rightside-left-chat'">
            <span><i class="fa fa-circle" aria-hidden="true"></i>{{message.author==='you' ? 'You' : 'Yoda'}}<small>10:00 AM,Today</small> </span><br><br>
            <p>{{message.text}}</p>
        </div>
    </li>
</template>
<script>
    export default {
        props: ['message']
    }
</script>