document.addEventListener('turbo:load', () => {
    const collectionHolder = document.getElementById('entries-wrapper');
    const addEntryBtn = document.getElementById('add-entry-btn');

    // ✅ Protection à l’intérieur du turbo:load
    if (!collectionHolder || !addEntryBtn) return;

    // Supprimer les anciennes lignes avec la classe .journal-entry-row
    const initialRows = collectionHolder.querySelectorAll('.journal-entry-row');
    initialRows.forEach(row => row.remove());

    let index = collectionHolder.querySelectorAll('tr').length;

    addEntryBtn.addEventListener('click', () => {
        collectionHolder.querySelectorAll('.journal-entry-row').forEach(row => row.remove());

        const prototype = collectionHolder.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, index);

        const newRow = document.createElement('tr');
        newRow.classList.add('journal-entry-row');
        newRow.innerHTML = newForm;

        collectionHolder.appendChild(newRow);
        index++;

        addEntryBtn.disabled = true;
    });
});


