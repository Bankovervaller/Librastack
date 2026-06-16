(function () {
    const form = document.querySelector('[data-google-books-form]');
    if (!form) {
        return;
    }

    const inputs = Array.from(form.querySelectorAll('.google-books-input'));
    const titleInput = form.querySelector('#naam');
    const authorInput = form.querySelector('#auteur');
    const isbnInput = form.querySelector('#isbn');
    let abortController = null;
    let debounceTimer = null;
    let activeList = null;
    let activeItems = [];
    let activeIndex = -1;

    function endpoint(query, type) {
        const url = new URL(window.location.origin + window.location.pathname);
        url.search = '?controller=book&action=googleBooks&q=' + encodeURIComponent(query) + '&type=' + encodeURIComponent(type || 'all');
        return url.toString();
    }

    function clearSuggestions() {
        form.querySelectorAll('.google-books-suggestions').forEach(list => {
            list.innerHTML = '';
            list.style.display = 'none';
        });
        activeList = null;
        activeItems = [];
        activeIndex = -1;
        inputs.forEach((input) => input.setAttribute('aria-expanded', 'false'));
    }

    function activeQuery(input) {
        const value = input.value.trim();
        if (value.length < 2) {
            return '';
        }

        return value;
    }

    function render(input, books) {
        clearSuggestions();

        const field = input.closest('.google-books-field');
        const list = field ? field.querySelector('.google-books-suggestions') : null;
        if (!list) {
            return;
        }

        if (books && books.error) {
            renderMessage(input, books.message || 'Google Books is nu niet bereikbaar.');
            return;
        }

        if (!Array.isArray(books) || books.length === 0) {
            renderMessage(input, 'Geen Google Books suggesties gevonden.');
            return;
        }

        books.forEach((book) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action google-books-suggestion';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');

            const title = document.createElement('span');
            title.className = 'google-books-suggestion-title';
            title.textContent = book.title || 'Onbekende titel';

            const meta = document.createElement('span');
            meta.className = 'google-books-suggestion-meta';
            meta.textContent = [book.author, book.isbn, book.publishedDate].filter(Boolean).join(' · ');

            item.appendChild(title);
            if (meta.textContent !== '') {
                item.appendChild(meta);
            }

            item.addEventListener('mousedown', (event) => {
                event.preventDefault();
                applyBook(book);
                clearSuggestions();
            });

            list.appendChild(item);
        });

        activeList = list;
        activeItems = Array.from(list.querySelectorAll('[role="option"]'));
        activeIndex = 0; // Default to first item
        updateActive();
        list.style.display = 'block';
        input.setAttribute('aria-expanded', 'true');
    }

    function applyBook(book) {
        if (titleInput && book.title) {
            titleInput.value = book.title;
        }
        if (authorInput && book.author) {
            authorInput.value = book.author;
        }
        if (isbnInput && book.isbn) {
            isbnInput.value = book.isbn;
        }
    }

    function search(input) {
        const query = activeQuery(input);
        if (query === '') {
            clearSuggestions();
            return;
        }

        const type = input.dataset.googleBooksType || 'all';

        if (abortController) {
            abortController.abort();
        }

        input.classList.add('loading');

        abortController = new AbortController();
        fetch(endpoint(query, type), { signal: abortController.signal })
            .then((response) => response.ok ? response.json() : [])
            .then((books) => {
                input.classList.remove('loading');
                render(input, books);
            })
            .catch((error) => {
                input.classList.remove('loading');
                if (error.name !== 'AbortError') {
                    renderError(input);
                }
            });
    }

    function renderError(input) {
        renderMessage(input, 'Google Books is nu niet bereikbaar.');
    }

    function renderMessage(input, message) {
        clearSuggestions();

        const field = input.closest('.google-books-field');
        const list = field ? field.querySelector('.google-books-suggestions') : null;
        if (!list) {
            return;
        }

        const item = document.createElement('div');
        item.className = 'list-group-item text-muted';
        item.textContent = message;
        list.appendChild(item);
        activeList = list;
        list.style.display = 'block';
        input.setAttribute('aria-expanded', 'true');
    }

    function updateActive() {
        activeItems.forEach((item, index) => {
            const selected = index === activeIndex;
            item.classList.toggle('active', selected);
            item.setAttribute('aria-selected', selected ? 'true' : 'false');
            if (selected) {
                item.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    inputs.forEach((input) => {
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');

        input.addEventListener('focus', () => {
            // Optional: trigger search on focus if input has value
            // But definitely clear others
            const currentList = input.closest('.google-books-field').querySelector('.google-books-suggestions');
            if (activeList !== currentList) {
                clearSuggestions();
            }
        });

        input.addEventListener('input', () => {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            debounceTimer = setTimeout(() => search(input), 150);
        });

        input.addEventListener('keydown', (event) => {
            if (!activeItems.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = Math.min(activeItems.length - 1, activeIndex + 1);
                updateActive();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = Math.max(0, activeIndex - 1);
                updateActive();
            } else if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                activeItems[activeIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
            } else if (event.key === 'Escape') {
                clearSuggestions();
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) {
            clearSuggestions();
        }
    });
})();
