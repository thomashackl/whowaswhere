<h1><?= dgettext('whowaswhere', 'Suchen Sie hier nach Personen') ?></h1>
<form class="default" action="<?= $controller->url_for('search/results') ?>" method="post">
    <section>
        <?= $search ?>
    </section>
    <section>
        <label>
            <?= dgettext('whowaswhere', 'Semester einschränken:') ?>
            <br/>
            <select name="start_time">
                <option value=""><?= dgettext('whowaswhere', 'Alle Semester') ?></option>
                <?php foreach ($semesters as $s) { ?>
                <option value="<?= $s->beginn ?>"><?= htmlReady($s->name) ?></option>
                <?php } ?>
            </select>
        </label>
    </section>
    <footer>
        <?= Studip\Button::createAccept(dgettext('whowaswhere', 'Suche starten'), 'submit') ?>
    </footer>
</form>
