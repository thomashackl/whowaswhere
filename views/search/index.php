<h1><?= dgettext('whowaswhere', 'Suchen Sie hier nach Personen') ?></h1>
<form action="<?= $controller->url_for('search/results') ?>" method="post">
    <?= $search ?>
    <br/><br/>
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
    <br/><br/>
    <?= Studip\Button::createAccept(dgettext('whowaswhere', 'Suche starten'), 'submit') ?>
</form>