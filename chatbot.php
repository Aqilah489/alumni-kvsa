<?php
session_start();
require_once 'connection.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$page_title = 'AI Assistant';
include_once 'kaunseling/includes/header.php';
?>

<style>
.chat-container {
    max-width: 700px;
    margin: 30px auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}
.chat-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 20px;
    text-align: center;
}
.chat-box {
    height: 450px;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}
.message-bot {
    background: white;
    border: 1px solid #ddd;
    padding: 10px 15px;
    border-radius: 20px 20px 20px 5px;
    max-width: 80%;
    margin-bottom: 15px;
    display: flex;
    gap: 10px;
}
.message-user {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 10px 15px;
    border-radius: 20px 20px 5px 20px;
    max-width: 80%;
    margin-left: auto;
    margin-bottom: 15px;
}
.typing-indicator {
    background: white;
    padding: 10px 15px;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 15px;
}
.typing-indicator span {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #999;
    margin: 0 2px;
    animation: typing 1.4s infinite;
}
@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
    30% { transform: translateY(-10px); opacity: 1; }
}
.suggestion-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 15px;
    background: white;
    border-top: 1px solid #ddd;
}
.suggestion-btn {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
}
.suggestion-btn:hover {
    background: #667eea;
    color: white;
}
.input-area {
    display: flex;
    padding: 15px;
    background: white;
    border-top: 1px solid #ddd;
    gap: 10px;
}
.input-area input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 25px;
    outline: none;
}
.input-area input:focus {
    border-color: #667eea;
}
.input-area button {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
}
.input-area button:hover {
    opacity: 0.9;
}
</style>

<div class="container">
    <div class="chat-container">
        <div class="chat-header">
            <i class="bi bi-robot" style="font-size: 2rem;"></i>
            <h3 class="mt-2">AI Assistant</h3>
            <p class="mb-0">Saya sedia membantu 24/7</p>
        </div>
        
        <div class="chat-box" id="chatBox">
            <div class="message-bot">
                <i class="bi bi-robot fs-5"></i>
                <div>👋 Selamat datang! Saya AI Assistant.<br>Tanya saya apa-apa tentang sistem alumni.</div>
            </div>
        </div>
        
        <div class="suggestion-buttons">
            <span class="suggestion-btn" data-question="Macam mana nak kemaskini profil?">📝 Kemaskini Profil</span>
            <span class="suggestion-btn" data-question="Saya lupa password">🔑 Lupa Password</span>
            <span class="suggestion-btn" data-question="Berapa jumlah alumni?">📊 Jumlah Alumni</span>
            <span class="suggestion-btn" data-question="Berapa yang dah bekerja?">💼 Status Kerja</span>
            <span class="suggestion-btn" data-question="Batch terkini?">📅 Batch Terkini</span>
            <span class="suggestion-btn" data-question="Hello">👋 Hello</span>
        </div>
        
        <div class="input-area">
            <input type="text" id="userInput" placeholder="Taip soalan anda..." onkeypress="if(event.key==='Enter') sendMessage()">
            <button onclick="sendMessage()"><i class="bi bi-send"></i> Hantar</button>
        </div>
    </div>
</div>

<script>

// REAL AI - Panggil Ollama API (Local)
async function getRealAIResponse(question) {
    try {
        const response = await fetch('ollama_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'question=' + encodeURIComponent(question)
        });
        const data = await response.json();
        return data.response;
    } catch(error) {
        console.error('Ollama Error:', error);
        return "Maaf, AI assistant sedang sibuk. Sila cuba sebentar lagi.";
    }
}

function sendMessage() {
    let input = document.getElementById('userInput');
    let message = input.value.trim();
    if(message === '') return;
    
    // Display user message
    displayMessage(message, 'user');
    input.value = '';
    
    // Show typing indicator
    showTyping();
    
    // Simulate AI thinking (delay 0.5-1 second)
    setTimeout(() => {
        let response = getBotResponse(message);
        hideTyping();
        displayMessage(response, 'bot');
    }, 500);
}

function displayMessage(message, sender) {
    let chatBox = document.getElementById('chatBox');
    let messageDiv = document.createElement('div');
    
    if(sender === 'user') {
        messageDiv.className = 'message-user';
        messageDiv.innerHTML = message;
    } else {
        messageDiv.className = 'message-bot';
        messageDiv.innerHTML = '<i class="bi bi-robot fs-5"></i><div>' + message + '</div>';
    }
    
    chatBox.appendChild(messageDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function showTyping() {
    let chatBox = document.getElementById('chatBox');
    let typingDiv = document.createElement('div');
    typingDiv.id = 'typingIndicator';
    typingDiv.className = 'typing-indicator';
    typingDiv.innerHTML = '<span></span><span></span><span></span>';
    chatBox.appendChild(typingDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function hideTyping() {
    let typingDiv = document.getElementById('typingIndicator');
    if(typingDiv) typingDiv.remove();
}

// Suggestion buttons
document.querySelectorAll('.suggestion-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('userInput').value = this.getAttribute('data-question');
        sendMessage();
    });
});
</script>

<?php include_once 'kaunseling/includes/footer.php'; ?>