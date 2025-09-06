document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('add-entry-btn');
    const wrapper = document.getElementById('entries-wrapper');
    const prototype = wrapper.dataset.prototype;
    let index = wrapper.querySelectorAll('.journal-entry-row').length;

    addButton.addEventListener('click', function() {
        const newRow = prototype.replace(/__name__/g, index);
        wrapper.insertAdjacentHTML('beforeend', newRow);
        index++;
    });
});

