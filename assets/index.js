document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('questionsContainer');
    const addQuestionBtn = document.getElementById('addQuestion');
    let questionCount = 0;

    const createChoiceRow = (questionIndex, choiceIndex) => {
        const row = document.createElement('div');
        row.className = 'choice-row';

        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = `questions[${questionIndex}][answer]`;
        radio.value = choiceIndex;
        radio.required = true;

        const input = document.createElement('input');
        input.type = 'text';
        input.name = `questions[${questionIndex}][choices][${choiceIndex}]`;
        input.placeholder = `Вариант ${choiceIndex + 1}`;
        input.required = true;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '✕';
        removeBtn.className = 'tiny danger';
        removeBtn.addEventListener('click', () => {
            const parent = row.parentElement;
            if (parent.querySelectorAll('.choice-row').length <= 2) {
                alert('В каждом вопросе должно быть минимум два варианта ответа.');
                return;
            }
            if (radio.checked) {
                const firstRadio = parent.querySelector('input[type="radio"]');
                if (firstRadio) {
                    firstRadio.checked = true;
                }
            }
            row.remove();
            renumberChoices(parent, questionIndex);
        });

        row.appendChild(radio);
        row.appendChild(input);
        row.appendChild(removeBtn);
        return row;
    };

    const renumberChoices = (choicesContainer, questionIndex) => {
        const rows = Array.from(choicesContainer.querySelectorAll('.choice-row'));
        rows.forEach((row, idx) => {
            const radio = row.querySelector('input[type="radio"]');
            const input = row.querySelector('input[type="text"]');
            radio.value = idx;
            input.name = `questions[${questionIndex}][choices][${idx}]`;
            input.placeholder = `Вариант ${idx + 1}`;
        });
    };

    const addChoice = (choicesContainer, questionIndex) => {
        const idx = choicesContainer.querySelectorAll('.choice-row').length;
        const choiceRow = createChoiceRow(questionIndex, idx);
        choicesContainer.appendChild(choiceRow);
    };

    const createQuestionBlock = (index) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'question-block';

        const header = document.createElement('div');
        header.className = 'question-header';
        const title = document.createElement('h3');
        title.textContent = `Вопрос ${index + 1}`;
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = 'Удалить';
        remove.className = 'tiny danger';
        remove.addEventListener('click', () => {
            wrapper.remove();
            updateQuestionTitles();
        });
        header.appendChild(title);
        header.appendChild(remove);

        const questionInput = document.createElement('textarea');
        questionInput.name = `questions[${index}][text]`;
        questionInput.placeholder = 'Текст вопроса';
        questionInput.rows = 2;
        questionInput.required = true;

        const choicesContainer = document.createElement('div');
        choicesContainer.className = 'choices';

        for (let i = 0; i < 4; i += 1) {
            const choiceRow = createChoiceRow(index, i);
            choicesContainer.appendChild(choiceRow);
            if (i === 0) {
                choiceRow.querySelector('input[type="radio"]').checked = true;
            }
        }

        const addChoiceBtn = document.createElement('button');
        addChoiceBtn.type = 'button';
        addChoiceBtn.textContent = 'Добавить вариант';
        addChoiceBtn.className = 'tiny';
        addChoiceBtn.addEventListener('click', () => {
            addChoice(choicesContainer, index);
        });

        wrapper.appendChild(header);
        wrapper.appendChild(questionInput);
        wrapper.appendChild(choicesContainer);
        wrapper.appendChild(addChoiceBtn);

        return wrapper;
    };

    const updateQuestionTitles = () => {
        const blocks = Array.from(container.querySelectorAll('.question-block'));
        blocks.forEach((block, idx) => {
            block.querySelector('h3').textContent = `Вопрос ${idx + 1}`;
            block.querySelector('textarea').name = `questions[${idx}][text]`;
            const choicesContainer = block.querySelector('.choices');
            choicesContainer.querySelectorAll('.choice-row').forEach((row, choiceIdx) => {
                row.querySelector('input[type="radio"]').name = `questions[${idx}][answer]`;
                row.querySelector('input[type="radio"]').value = choiceIdx;
                row.querySelector('input[type="text"]').name = `questions[${idx}][choices][${choiceIdx}]`;
                row.querySelector('input[type="text"]').placeholder = `Вариант ${choiceIdx + 1}`;
            });
        });
        questionCount = blocks.length;
    };

    addQuestionBtn.addEventListener('click', () => {
        const block = createQuestionBlock(questionCount);
        container.appendChild(block);
        questionCount += 1;
    });

    // Добавляем первый вопрос по умолчанию
    addQuestionBtn.click();
});
