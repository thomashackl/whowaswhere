<form class="default" action="<?= $controller->url_for('export/do') ?>" method="post">
    <header>
        <h1>
            <?= dgettext('whowaswhere', 'Welche Veranstaltungen sollen im Export ausgegeben werden?') ?>
        </h1>
        <p>
            <?= dgettext('whowaswhere', 'Es sind nur Veranstaltungen bis zum aktuellen Semester auswählbar.') ?>
        </p>
    </header>
    <?php foreach ($courses as $semester => $data) : ?>
        <table class="default">
            <caption>
                <?= htmlReady($semester) ?>
            </caption>
            <colgroup>
                <col width="15">
                <col width="75">
                <col>
                <col width="200">
            </colgroup>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" data-proxyfor=":checkbox.courses-<?=
                        strtolower(str_replace(array(' ', '/'), '', htmlReady($semester))) ?>">
                    </th>
                    <th><?= _('Nummer') ?></th>
                    <th><?= _('Name') ?></th>
                    <th><?= _('Typ') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $course) : ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="courses[]" class="courses-<?=
                                strtolower(str_replace(array(' ', '/'), '',
                                htmlReady($semester))) ?>" value="<?= $course['Seminar_id'] ?>" checked>
                        </td>
                        <td>
                            <?= htmlReady($course['VeranstaltungsNummer']) ?>
                        </td>
                        <td>
                            <?= htmlReady($course['Name']) ?>
                        </td>
                        <td>
                            <?= htmlReady($course['type']) ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endforeach ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Exportieren'), 'export') ?>
        <?= Studip\LinkButton::createCancel(_('Schließen'), URLHelper::getURL('dispatch.php/my_courses')) ?>
    </footer>
</form>
