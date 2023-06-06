<template>
    <div class="container">
        <div class="chat-container" ref="chatContainer">

            <div class="command-container">
                <p>Running the following command will push something to the websocket & you can see the result in this chat area</p>
                <code>
                    php artisan push:notification
                </code>
            </div>


            <div v-for="message in messages" class="chat-message-pill"
                 :class="{sender : message.sender === currentUserID, receiver : message.sender !== currentUserID}">
                <span>{{ message.message }}</span>
            </div>
        </div>


        <div class="chat-input">
            <input type="text" v-model="message" placeholder="Type your message..." @keydown.enter="sendMessage">
            <button @click="sendMessage">Send</button>
        </div>
    </div>
</template>

<style scoped>
.container {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.chat-container {
    width: 100%;
    flex-grow: 1;
    overflow-y: scroll;
    display: flex;
    flex-direction: column;
}

.command-container{
    font-size: small;
    align-self: center;
    text-align: center;
}

.chat-input {
    margin: 20px;
    display: flex;
    align-items: center;
}

.sender {
    align-self: end;
}

.receiver {
    align-self: start;
}

.chat-message-pill {
    display: inline-block;
    max-width: 70%;
    padding: 8px 16px;
    margin: 4px 0;
    border-radius: 20px;
    background-color: aqua;
    word-break: break-word;
}

.chat-input input[type="text"] {
    flex-grow: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.chat-input button {
    margin-left: 8px;
    padding: 8px 16px;
    background-color: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.chat-input button:hover {
    background-color: #45a049;
}
</style>


<script>
import {onMounted, ref} from 'vue'
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios'

export default {
    setup() {

        //configure Laravel Echo
        window.Pusher = Pusher;
        const configuredEcho = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            wsHost: import.meta.env.VITE_PUSHER_HOST,
            wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
            wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });


        const message = ref('')
        const messages = ref([])
        const currentUserID = ref(generateBrowserId());

        function generateBrowserId() {
            const timestamp = Date.now().toString();
            const userAgent = navigator.userAgent;
            const randomValue = Math.random().toString();
            return window.btoa(timestamp + userAgent + randomValue);
        }

        function sendMessage() {
            if (!this.message) {
                return;
            }

            const endpoint = import.meta.env.VITE_APP_URL + '/api/messages';
            axios.post(endpoint, {
                sender: this.currentUserID,
                message: this.message
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            }).then((response) => {
                console.log(response);
                this.message = '';
            }).catch((error) => {
                console.log(error);
            });
        }

        onMounted(() => {
            configuredEcho.channel("chat")
                .listen("NewMessage", (e) => {
                    console.log(e)
                    messages.value.push({
                        sender: e.sender,
                        message: e.message
                    })
                })
        })

        return {
            message,
            messages,
            currentUserID,
            sendMessage
        }
    },
}
</script>

