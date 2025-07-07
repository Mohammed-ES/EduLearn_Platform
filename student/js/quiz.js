// quiz.js

const GEMINI_API_KEY = 'GEMINI_API_KEY';
const endpoint = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${GEMINI_API_KEY}`;

const modules = {
  'POO': ['What is encapsulation?', 'Explain polymorphism in OOP.'],
  'Networks': ['Define IP addressing.', 'What is subnetting?'],
  'Biology': ['Describe cell respiration.', 'What is DNA replication?']
};

function generateQuiz() {
  const module = document.getElementById('moduleSelect').value;
  const questions = modules[module] || [];

  const quizSection = document.getElementById('quizSection');
  quizSection.innerHTML = '';

  questions.forEach((q, index) => {
    quizSection.innerHTML += `
      <div class="quiz-question">
        <h3>Q${index + 1}: ${q}</h3>
        <textarea id="answer-${index}" rows="4" placeholder="Your answer here..."></textarea>
      </div>
    `;
  });

  quizSection.innerHTML += `<button class="submit-btn" onclick="submitQuiz('${module}', ${questions.length})">Submit Answers</button>`;
}

function submitQuiz(module, total) {
  let answersText = `Correct and give advice on the following answers for module "${module}":\n\n`;

  for (let i = 0; i < total; i++) {
    const answer = document.getElementById(`answer-${i}`).value;
    answersText += `Q${i + 1}: ${answer}\n`;
  }

  fetch(endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      contents: [{
        parts: [{ text: answersText }]
      }]
    })
  })
  .then(res => res.json())
  .then(data => {
    const feedback = data?.candidates?.[0]?.content?.parts?.[0]?.text || 'No feedback received.';
    displayFeedback(feedback);
  })
  .catch(() => displayFeedback('An error occurred while connecting to AI service.'));
}

function displayFeedback(text) {
  const feedback = document.createElement('div');
  feedback.className = 'feedback-area';
  feedback.innerHTML = `<h3>AI Feedback:</h3><p>${text.replace(/\n/g, '<br>')}</p>`;
  document.getElementById('quizSection').appendChild(feedback);
}

// Populate module dropdown
document.addEventListener('DOMContentLoaded', () => {
  const select = document.getElementById('moduleSelect');
  for (let mod in modules) {
    const opt = document.createElement('option');
    opt.value = mod;
    opt.textContent = mod;
    select.appendChild(opt);
  }
});
