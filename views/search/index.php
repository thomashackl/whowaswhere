<h1><?= dgettext('whowaswhere', 'Suchen Sie hier nach Personen') ?></h1>
<form class="default" action="<?= $controller->url_for('search/results') ?>" method="post">
    <section>
        <?= $search ?>
    </section>
    <section>
        <label for="start-time">
            <?= dgettext('whowaswhere', 'Semester einschränken:') ?>
        </label>
        <select name="start_time" id="start-time">
            <option value=""><?= dgettext('whowaswhere', 'Alle Semester') ?></option>
            <?php foreach ($semesters as $s) { ?>
                <option value="<?= $s->beginn ?>"><?= htmlReady($s->name) ?></option>
            <?php } ?>
        </select>
    </section>
    <section>
        <label>
            <input type="checkbox" name="awaiting">
            <?= dgettext('whowaswhere', 'Wartelisteneinträge einschließen') ?>
        </label>
    </section>
    <section>
        <label>
            <input type="checkbox" name="accepted">
            <?= dgettext('whowaswhere', 'Vorläufig akzeptiert-Einträge einschließen') ?>
        </label>
    </section>
    <footer>
        <?= Studip\Button::createAccept(dgettext('whowaswhere', 'Suche starten'), 'submit') ?>
    </footer>
</form>
