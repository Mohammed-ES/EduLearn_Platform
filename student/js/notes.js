// notes.js

let modules = {};

function openAddModuleModal() {
  document.getElementById('addModuleModal').style.display = 'block';
}

function closeAddModuleModal() {
  document.getElementById('addModuleModal').style.display = 'none';
}

function openNoteModal(moduleName, noteIndex = null) {
  document.getElementById('noteModal').style.display = 'block';
  document.getElementById('noteModalTitle').innerText = noteIndex === null ? 'Add Note' : 'Edit Note';
  document.getElementById('noteTitle').value = '';
  document.getElementById('noteContent').value = '';

  if (noteIndex !== null) {
    const note = modules[moduleName].notes[noteIndex];
    document.getElementById('noteTitle').value = note.title;
    document.getElementById('noteContent').value = note.content;
  }

  document.getElementById('noteModal').dataset.module = moduleName;
  document.getElementById('noteModal').dataset.index = noteIndex;
}

function closeNoteModal() {
  document.getElementById('noteModal').style.display = 'none';
}

function addModule() {
  const name = document.getElementById('moduleName').value.trim();
  if (!name) return;
  modules[name] = { notes: [] };
  renderModules();
  closeAddModuleModal();
  document.getElementById('moduleName').value = '';
}

function saveNote() {
  const moduleName = document.getElementById('noteModal').dataset.module;
  const index = document.getElementById('noteModal').dataset.index;
  const title = document.getElementById('noteTitle').value.trim();
  const content = document.getElementById('noteContent').value.trim();

  if (!title || !content) return;

  if (index === 'null') {
    modules[moduleName].notes.push({ title, content });
  } else {
    modules[moduleName].notes[index] = { title, content };
  }

  renderModules();
  closeNoteModal();
}

function deleteNote(module, index) {
  Swal.fire({
    title: 'Delete this note?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#007BFF',
    confirmButtonText: 'Yes, delete it!'
  }).then(result => {
    if (result.isConfirmed) {
      modules[module].notes.splice(index, 1);
      renderModules();
    }
  });
}

function renderModules() {
  const container = document.getElementById('modulesList');
  container.innerHTML = '';

  Object.keys(modules).forEach((moduleName) => {
    const module = modules[moduleName];
    const moduleDiv = document.createElement('div');
    moduleDiv.className = 'module-block';

    const header = document.createElement('div');
    header.className = 'module-header';
    header.innerHTML = `<h3>${moduleName}</h3><button onclick=\"openNoteModal('${moduleName}')\">+ Add Note</button>`;
    moduleDiv.appendChild(header);

    module.notes.forEach((note, i) => {
      const noteDiv = document.createElement('div');
      noteDiv.className = 'note-card';
      noteDiv.innerHTML = `
        <h4>${note.title}</h4>
        <p>${note.content}</p>
        <div class=\"actions\">
          <button onclick=\"openNoteModal('${moduleName}', ${i})\">Edit</button>
          <button onclick=\"deleteNote('${moduleName}', ${i})\">Delete</button>
        </div>
      `;
      moduleDiv.appendChild(noteDiv);
    });

    container.appendChild(moduleDiv);
  });
}
