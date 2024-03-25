document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('metaInputsContainer');
    const addBtn = document.getElementById('addMetaPair');
    const removeBtn = document.getElementById('removeMetaPair');

    addBtn.onclick = function() {
        const newPair = document.querySelector('.metaInputPair').cloneNode(true);
        newPair.querySelectorAll('input').forEach(input => input.value = '');
        container.appendChild(newPair);
    };

    removeBtn.onclick = function() {
        const pairs = container.querySelectorAll('.metaInputPair');
        if (pairs.length > 1) {
            pairs[pairs.length - 1].remove();
        }
    };
});