// public/assets/js/app.js — интерактивность проекта «Ташкили Конфронсии илмӣ»
// Подключается как ES-модуль: <script type="module" src="/assets/js/app.js"></script>
// Зависимости: только Bootstrap 5.3 (bootstrap.bundle.min.js подключён раньше, объект window.bootstrap).

/* ==========================================================================
   1. Общие утилиты
   ========================================================================== */

// Откладывает вызов fn, пока пользователь не сделает паузу delay мс.
function debounce(fn, delay = 300) {
    let timerId;
    return (...args) => {
        clearTimeout(timerId);
        timerId = setTimeout(() => fn(...args), delay);
    };
}

// 'YYYY-MM-DD' -> 'DD.MM.YYYY' (пустое значение -> '')
function formatDate(isoDate) {
    if (!isoDate) return '';
    const [year, month, day] = isoDate.split('-');
    return `${day}.${month}.${year}`;
}

/* ==========================================================================
   2. Валидация формы подачи доклада / статьи
   ========================================================================== */

const FIELD_MESSAGES = {
    title: {
        valueMissing: 'Укажите название научного доклада.',
        tooShort: 'Название доклада должно содержать минимум 10 символов.',
    },
    author_name: {
        valueMissing: 'Укажите ФИО докладчика.',
    },
    author_phone: {
        valueMissing: 'Укажите контактный телефон.',
        patternMismatch: 'Введите номер в формате +992 XX XXX XX XX.',
    },
    section: {
        valueMissing: 'Выберите научную секцию конференции.',
    },
    presentation_date: {
        valueMissing: 'Укажите планируемую дату выступления.',
    },
    end_date: {
        valueMissing: 'Укажите дату завершения сессии.',
    },
    abstract: {
        tooLong: 'Аннотация не должна превышать 500 символов.',
    },
};

// Текст ошибки для поля на основе его ValidityState ('' — ошибок нет).
function messageFor(field) {
    const v = field.validity;
    if (v.valid) return '';
    if (v.customError) return field.validationMessage;

    const custom = FIELD_MESSAGES[field.name] ?? {};
    if (v.valueMissing) return custom.valueMissing ?? 'Заполните это поле.';
    if (v.tooShort) return custom.tooShort ?? `Минимум ${field.minLength} символов.`;
    if (v.tooLong) return custom.tooLong ?? `Не более ${field.maxLength} символов.`;
    if (v.patternMismatch) return custom.patternMismatch ?? 'Неверный формат данных.';
    if (v.badInput) return 'Введите корректное значение.';
    if (v.rangeUnderflow) return 'Значение меньше допустимого.';
    if (v.typeMismatch) return 'Неверный формат.';
    return 'Проверьте значение поля.';
}

// Показывает/скрывает ошибку у поля. Возвращает true, если поле корректно.
function showFieldState(field) {
    const message = messageFor(field);
    const feedback = document.getElementById(`${field.id}-error`);

    field.classList.toggle('is-invalid', message !== '');
    if (message) {
        field.setAttribute('aria-invalid', 'true');
    } else {
        field.removeAttribute('aria-invalid');
    }
    if (feedback) feedback.textContent = message;

    return message === '';
}

// Собственные правила через setCustomValidity():
// 1) Дата выступления не раньше 25.11.2026 и дата завершения не раньше даты выступления.
// 2) Научный руководитель не должен совпадать с ФИО докладчика.
function checkArticleRules(form) {
    const start = form.elements.presentation_date;
    const end = form.elements.end_date;
    const author = form.elements.author_name;
    const coauthor = form.elements.coauthor_name;

    if (start) {
        start.setCustomValidity('');
        if (start.value && start.value < '2026-11-25') {
            start.setCustomValidity('Дата выступления не может быть раньше открытия конференции (25.11.2026).');
        }
    }

    if (end) {
        end.setCustomValidity('');
        if (start?.value && end.value && end.value < start.value) {
            end.setCustomValidity('Дата завершения сессии не может быть раньше даты выступления.');
        }
    }

    if (coauthor) {
        coauthor.setCustomValidity('');
        const authorVal = (author?.value ?? '').trim().toLowerCase();
        const coauthorVal = coauthor.value.trim().toLowerCase();
        if (authorVal !== '' && coauthorVal !== '' && authorVal === coauthorVal) {
            coauthor.setCustomValidity('ФИО научного руководителя не должно совпадать с ФИО докладчика.');
        }
    }
}

function initArticleForm() {
    const form = document.getElementById('article-form');
    if (!form) return;

    const { presentation_date: start, end_date: end } = form.elements;
    const fields = () => form.querySelectorAll('input, select, textarea');

    if (start && end) {
        start.addEventListener('change', () => {
            end.min = start.value;
        });
    }

    const refresh = (field) => {
        checkArticleRules(form);
        showFieldState(field);
        if (end && field !== end && end.classList.contains('is-invalid')) {
            showFieldState(end);
        }
        const coauthor = form.elements.coauthor_name;
        if (coauthor && field !== coauthor && coauthor.classList.contains('is-invalid')) {
            showFieldState(coauthor);
        }
    };

    form.addEventListener('focusout', (event) => {
        if (event.target.matches('input, select, textarea')) {
            refresh(event.target);
        }
    });

    form.addEventListener('input', (event) => {
        if (event.target.classList.contains('is-invalid')) {
            refresh(event.target);
        }
    });

    form.addEventListener('submit', (event) => {
        checkArticleRules(form);
        let firstInvalid = null;

        fields().forEach((field) => {
            if (!showFieldState(field) && !firstInvalid) {
                firstInvalid = field;
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
        }
    });
}

/* ==========================================================================
   3. Живой поиск научных статей / докладов
   ========================================================================== */

const STATUS_LABELS = {
    accepted: 'Принята',
    under_review: 'На рецензии',
    revision: 'На доработке',
    rejected: 'Отклонена',
};

const STATUS_CLASSES = {
    accepted: 'bg-success-subtle text-success-emphasis',
    under_review: 'bg-warning-subtle text-warning-emphasis',
    revision: 'bg-info-subtle text-info-emphasis',
    rejected: 'bg-danger-subtle text-danger-emphasis',
};

function createCell(text, className = '') {
    const td = document.createElement('td');
    td.textContent = text ?? '';
    if (className) td.className = className;
    return td;
}

function createArticleRow(article) {
    const tr = document.createElement('tr');

    const titleCell = document.createElement('td');
    const link = document.createElement('a');
    link.href = `/static/article.html?id=${encodeURIComponent(article.id)}`;
    link.className = 'fw-medium text-decoration-none';
    link.textContent = article.title;
    titleCell.append(link);

    const statusCell = document.createElement('td');
    const badge = document.createElement('span');
    badge.className = 'badge rounded-pill';
    if (article.status in STATUS_CLASSES) {
        badge.classList.add(...STATUS_CLASSES[article.status].split(' '));
    }
    badge.textContent = STATUS_LABELS[article.status] ?? article.status;
    statusCell.append(badge);

    const actionsCell = document.createElement('td');
    actionsCell.className = 'text-end text-nowrap';

    const viewLink = document.createElement('a');
    viewLink.className = 'btn btn-sm btn-outline-secondary';
    viewLink.href = `/static/article.html?id=${encodeURIComponent(article.id)}`;
    viewLink.textContent = 'Открыть';

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'btn btn-sm btn-outline-danger ms-1';
    deleteButton.dataset.deleteUrl = `/articles/${encodeURIComponent(article.id)}/delete`;
    deleteButton.dataset.articleTitle = article.title;
    deleteButton.textContent = 'Удалить';

    actionsCell.append(viewLink, deleteButton);

    tr.append(
        titleCell,
        createCell(article.author),
        createCell(article.section, 'd-none d-md-table-cell'),
        createCell(formatDate(article.submitted_at), 'd-none d-lg-table-cell'),
        statusCell,
        actionsCell,
    );

    return tr;
}

function renderArticles(tbody, articles) {
    if (articles.length === 0) {
        const tr = document.createElement('tr');
        const td = createCell(
            'Доклады по заданному запросу не найдены. Измените условия поиска.',
            'text-center text-body-secondary py-4',
        );
        td.colSpan = 6;
        tr.append(td);
        tbody.replaceChildren(tr);
        return;
    }
    tbody.replaceChildren(...articles.map(createArticleRow));
}

function initArticleSearch() {
    const input = document.getElementById('article-search');
    const tbody = document.getElementById('articles-tbody');
    if (!input || !tbody) return;

    const statusBox = document.getElementById('article-search-status');
    const setStatus = (text) => {
        if (statusBox) statusBox.textContent = text;
    };

    let controller = null;

    async function search(query) {
        controller?.abort();
        controller = new AbortController();

        tbody.setAttribute('aria-busy', 'true');
        setStatus('Поиск...');

        try {
            const response = await fetch(`/api/articles?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const articles = await response.json();
            renderArticles(tbody, articles);
            setStatus(articles.length ? `Найдено докладов: ${articles.length}` : 'Доклады не найдены.');
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error('Ошибка живого поиска:', error);
            setStatus('Не удалось выполнить поиск. Проверьте соединение и попробуйте ещё раз.');
        } finally {
            tbody.removeAttribute('aria-busy');
        }
    }

    const debouncedSearch = debounce(search, 300);
    input.addEventListener('input', () => debouncedSearch(input.value.trim()));
}

/* ==========================================================================
   4. Модальное подтверждение удаления доклада
   ========================================================================== */

function initDeleteConfirm() {
    const modalElement = document.getElementById('confirm-delete-modal');
    if (!modalElement || !window.bootstrap) return;

    const form = document.getElementById('confirm-delete-form');
    const nameElement = document.getElementById('confirm-delete-name');
    const submitButton = form.querySelector('[type="submit"]');
    const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);

    let lastTrigger = null;

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-delete-url]');
        if (!button) return;

        lastTrigger = button;
        form.action = button.dataset.deleteUrl;
        nameElement.textContent = button.dataset.articleTitle ?? '';
        submitButton.disabled = false;
        modal.show();
    });

    modalElement.addEventListener('hidden.bs.modal', () => {
        if (lastTrigger?.isConnected) lastTrigger.focus();
    });

    form.addEventListener('submit', () => {
        submitButton.disabled = true;
    });
}

/* ==========================================================================
   5. Запуск модулей
   ========================================================================== */

initArticleForm();
initArticleSearch();
initDeleteConfirm();