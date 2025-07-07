// ai_assistant.js

const API_KEY = 'EXEMPLE_DE_CLE_API_POUR_DEMO';
const endpoint = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${API_KEY}`;

const moduleSelect = document.getElementById("moduleSelect");
const outputArea = document.getElementById("outputArea");

// Example modules (you can load dynamically from DB)
const modules = ["POO", "Networks", "Biology"];
modules.forEach(mod => {
  const opt = document.createElement("option");
  opt.value = mod;
  opt.textContent = mod;
  moduleSelect.appendChild(opt);
});

function getSummary() {
  const notes = document.getElementById("userNotes").value;
  if (!notes.trim()) return alert("Please enter your notes.");

  askGemini("Summarize and explain this:", notes);
}

function generateQuiz(type) {
  const notes = document.getElementById("userNotes").value;
  if (!notes.trim()) return alert("Please enter your notes first.");

  const prompt =
    type === 'mcq'
      ? "Generate a multiple-choice quiz from the following notes:"
      : "Generate a true/false quiz from these notes:";

  askGemini(prompt, notes);
}

function askGemini(prompt, content) {
  outputArea.innerHTML = "⏳ Please wait... AI is processing...";
  fetch(endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      contents: [{
        parts: [{ text: `${prompt}\n\n${content}` }]
      }]
    })
  })
    .then(res => res.json())
    .then(data => {
      const result = data?.candidates?.[0]?.content?.parts?.[0]?.text || "No response from AI.";
      outputArea.innerHTML = `<h3>AI Result:</h3><p>${result.replace(/\n/g, "<br>")}</p>`;
    })
    .catch(() => {
      outputArea.innerHTML = "❌ Error connecting to Gemini API.";
    });
}

function exportContent() {
  const content = outputArea.innerText;
  if (!content.trim()) return alert("No summary to export.");

  const blob = new Blob([content], { type: "text/plain" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "ai_summary.txt";
  a.click();
  URL.revokeObjectURL(url);
}
