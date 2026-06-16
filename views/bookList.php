<?php $pageTitle = 'Boeken'; ?>
<?php require 'inc/header.php'; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Boeken <span class="badge bg-secondary ms-2"><?php echo (int)($total ?? count($boekenArray ?? [])); ?></span></h1>
    <form class="input-group position-relative" style="max-width: 560px;" method="get" action="index.php" id="searchForm">
        <input type="hidden" name="controller" value="book">
        <input type="hidden" name="action" value="index">
        <input type="text" class="form-control" name="q" id="searchInput" placeholder="Titel, auteur of ISBN" aria-label="Zoek" aria-describedby="basic-addon1" aria-controls="searchSuggestions" aria-expanded="false" autocomplete="off" value="<?php echo htmlspecialchars($q ?? ''); ?>">
        <button class="btn btn-outline-secondary" type="submit">Zoek</button>
        <div id="searchSuggestions" class="list-group position-absolute w-100" style="top: 100%; z-index: 1000; display: none;" role="listbox" aria-label="Suggesties"></div>
    </form>
</div>

<div class="row g-2 mb-3">
    <div class="col-md-3">
        <form method="get" action="index.php" class="d-flex gap-2 align-items-end">
            <input type="hidden" name="controller" value="book">
            <input type="hidden" name="action" value="index">
            <input type="hidden" name="q" value="<?php echo $q ?? ''; ?>">
            <div class="flex-fill">
                <label for="sort" class="form-label small">Sorteren</label>
                <select id="sort" class="form-select" name="sort">
                    <option value="title" <?php echo ($sort ?? 'title') === 'title' ? 'selected' : ''; ?>>Titel</option>
                    <option value="author" <?php echo ($sort ?? '') === 'author' ? 'selected' : ''; ?>>Auteur</option>
                    <option value="isbn" <?php echo ($sort ?? '') === 'isbn' ? 'selected' : ''; ?>>ISBN</option>
                    <option value="date_added" <?php echo ($sort ?? '') === 'date_added' ? 'selected' : ''; ?>>Datum toegevoegd</option>
                </select>
            </div>
            <div style="max-width: 180px;">
                <label for="dir" class="form-label small">Richting</label>
                <select id="dir" class="form-select" name="dir">
                    <option value="ASC" <?php echo ($dir ?? 'ASC') === 'ASC' ? 'selected' : ''; ?>>Oplopend</option>
                    <option value="DESC" <?php echo ($dir ?? '') === 'DESC' ? 'selected' : ''; ?>>Aflopend</option>
                </select>
            </div>
            <div style="max-width: 140px;">
                <label for="limit" class="form-label small">Per pagina</label>
                <select id="limit" class="form-select" name="limit">
                    <?php foreach ([10,20,50] as $opt): ?>
                        <option value="<?php echo $opt; ?>" <?php echo ((int)($limit ?? 10)) === $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-outline-primary" type="submit">Toepassen</button>
        </form>
    </div>
</div>

<?php if (!empty($boekenArray) && count($boekenArray) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
            <tr>
                <th>Titel</th>
                <th>Auteur</th>
                <th>ISBN</th>
                <th class="text-end">Acties</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($boekenArray as $boek): ?>
                <tr>
                    <td><?php echo htmlspecialchars_decode($boek->title); ?></td>
                    <td><?php echo htmlspecialchars_decode($boek->author); ?></td>
                    <td><?php echo $boek->isbn; ?></td>
                    <td class="text-end">
                        <div class="btn-group" role="group" aria-label="Acties">
                            <a href="?id=<?php echo $boek->id; ?>" class="btn btn-sm btn-outline-info" title="Details">Details</a>
                            <a href="?pasaan=<?php echo $boek->id; ?>" class="btn btn-sm btn-outline-warning" title="Pas aan">Pas aan</a>
                            <a href="?verwijder=<?php echo $boek->id; ?>" class="btn btn-sm btn-outline-danger" title="Verwijder">Verwijder</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ((int)($total ?? 0) > (int)($limit ?? 10)): ?>
        <?php
            $curPage = (int)($page ?? 1);
            $pages = (int)($totalPages ?? 1);
            $base = 'index.php?controller=book&action=index';
            $qsBase = $base
                . '&q=' . urlencode($q ?? '')
                . '&sort=' . urlencode($sort ?? 'title')
                . '&dir=' . urlencode($dir ?? 'ASC')
                . '&limit=' . (int)($limit ?? 10);
            $prevUrl = $qsBase . '&page=' . max(1, $curPage - 1);
            $nextUrl = $qsBase . '&page=' . min($pages, $curPage + 1);
        ?>
        <nav aria-label="Pagina navigatie">
            <ul class="pagination justify-content-center">
                <?php $prevDisabled = $curPage <= 1; ?>
                <li class="page-item<?php echo $prevDisabled ? ' disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $prevUrl; ?>" tabindex="<?php echo $prevDisabled ? '-1' : '0'; ?>">Vorige</a>
                </li>
                <?php for ($p = 1; $p <= $pages; $p++):
                    $pageUrl = $qsBase . '&page=' . $p;
                ?>
                    <li class="page-item<?php echo $p === $curPage ? ' active' : ''; ?>">
                        <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
                <?php $nextDisabled = $curPage >= $pages; ?>
                <li class="page-item<?php echo $nextDisabled ? ' disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo htmlspecialchars($nextUrl); ?>" tabindex="<?php echo $nextDisabled ? '-1' : '0'; ?>">Volgende</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center">
            <p class="mb-3">Geen boeken gevonden.</p>
            <a href="index.php?voegtoe=true" class="btn btn-primary">Voeg een nieuw boek toe</a>
        </div>
    </div>
<?php endif; ?>

<?php require 'inc/footer.php'; ?>

<script>
(function(){
    const input = document.getElementById('searchInput');
    const list = document.getElementById('searchSuggestions');
    const form = document.getElementById('searchForm');
    let abortCtrl = null;
    let index = -1; // keyboard selection index
    let items = [];
    let debounceTimer = null;

    function clearSuggestions(){
        list.innerHTML = '';
        list.style.display = 'none';
        input.setAttribute('aria-expanded', 'false');
        items = [];
        index = -1;
    }

    function renderSuggestions(data){
        clearSuggestions();
        if (!data || !Array.isArray(data) || data.length === 0) return;
        data.forEach((s, i) => {
            const a = document.createElement('a');
            a.href = '#';
            a.className = 'list-group-item list-group-item-action';
            a.setAttribute('role', 'option');
            a.setAttribute('aria-selected', 'false');
            a.dataset.value = s.value;
            a.textContent = s.value + (s.type ? ' (' + s.type + ')' : '');
            a.addEventListener('mousedown', (e) => {
                e.preventDefault();
                input.value = s.value;
                clearSuggestions();
                form.submit();
            });
            list.appendChild(a);
        });
        items = Array.from(list.querySelectorAll('[role="option"]'));
        list.style.display = 'block';
        input.setAttribute('aria-expanded', 'true');
    }

    function fetchSuggestions(q){
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();
        const url = new URL(window.location.origin + window.location.pathname);
        url.search = '?controller=book&action=autocomplete&q=' + encodeURIComponent(q);
        fetch(url.toString(), { signal: abortCtrl.signal })
            .then(r => r.ok ? r.json() : [])
            .then(renderSuggestions)
            .catch(() => {});
    }

    input.addEventListener('input', () => {
        const q = input.value.trim();
        if (q.length < 2) { clearSuggestions(); return; }
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchSuggestions(q), 300);
    });

    input.addEventListener('keydown', (e) => {
        if (!items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            index = Math.min(items.length - 1, index + 1);
            updateActive();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            index = Math.max(0, index - 1);
            updateActive();
        } else if (e.key === 'Enter') {
            if (index >= 0) {
                e.preventDefault();
                input.value = items[index].dataset.value;
                clearSuggestions();
                form.submit();
            }
        } else if (e.key === 'Escape') {
            clearSuggestions();
        }
    });

    document.addEventListener('click', (e) => {
        if (!list.contains(e.target) && e.target !== input) {
            clearSuggestions();
        }
    });

    function updateActive(){
        items.forEach((el, i) => {
            if (i === index) {
                el.classList.add('active');
                el.setAttribute('aria-selected', 'true');
                el.scrollIntoView({ block: 'nearest' });
            } else {
                el.classList.remove('active');
                el.setAttribute('aria-selected', 'false');
            }
        });
    }
})();
</script>
