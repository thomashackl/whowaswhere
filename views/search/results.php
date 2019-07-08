<article>
    <header>
        <h1><?= htmlReady($user->getFullname()) ?> (<?= htmlReady($user->username) ?>)</h1>
    </header>
    <?php if ($matriculation) : ?>
        <section>
            <b><?= _('Matrikelnummer') ?>:</b> <?= htmlReady($matriculation) ?>
        </section>
    <?php endif ?>
    <?php if (count($user->studycourses) > 0) : ?>
    <section>
        <h2><?= dngettext('whowaswhere', 'Studiengang', 'Studiengänge', count($user->studycourses)) ?></h2>
        <?= $this->render_partial('search/_study_courses.php',
            array('studycourses' => $user->studycourses->filter(function ($s) {
                return $s->studycourse_name != 'Beschäftigte';
            }))) ?>
    </section>
    <?php endif ?>
    <section>
        <h2>
            <?= dgettext('whowaswhere', 'Veranstaltungen') ?>
            <?= tooltipIcon(sprintf(dngettext('whowaswhere',
                'Es werden nur Veranstaltungen der Kategorie "%s" angezeigt.',
                'Es werden nur Veranstaltungen der Kategorien "%s" angezeigt',
                count($categories)), implode('", "', $categories))) ?>
        </h2>
        <?php if (count($courses) > 0) : ?>
            <?php foreach ($courses as $semester => $entries) : ?>
                <table class="default">
                    <caption><?= htmlReady($semester) ?></caption>
                    <thead>
                    <tr>
                        <th width="10%"><?= dgettext('whowaswhere', 'Nummer') ?></th>
                        <th><?= dgettext('whowaswhere', 'Titel') ?></th>
                        <th width="15%"><?= dgettext('whowaswhere', 'Typ') ?></th>
                        <th width="15%"><?= dgettext('whowaswhere', 'Status') ?></th>
                        <th width="15%"><?= dgettext('whowaswhere', 'Eingetragen am') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($entries as $course) {
                        if ($course['status'] == 'dozent') {
                            switch ($user->geschlecht) {
                                case 1:
                                    $status = dgettext('whowaswhere', 'Dozent');
                                    break;
                                case 2:
                                    $status = dgettext('whowaswhere', 'Dozentin');
                                    break;
                                default:
                                    $status = dgettext('whowaswhere', 'Dozent/in');
                            }
                        } else if ($course['status'] == 'tutor') {
                            switch ($user->geschlecht) {
                                case 1:
                                    $status = dgettext('whowaswhere', 'Tutor');
                                    break;
                                case 2:
                                    $status = dgettext('whowaswhere', 'Tutorin');
                                    break;
                                default:
                                    $status = dgettext('whowaswhere', 'Tutor/in');
                            }
                        } else if ($course['status'] == 'autor') {
                            switch ($user->geschlecht) {
                                case 1:
                                    $status = dgettext('whowaswhere', 'Teilnehmer');
                                    break;
                                case 2:
                                    $status = dgettext('whowaswhere', 'Teilnehmerin');
                                    break;
                                default:
                                    $status = dgettext('whowaswhere', 'Teilnehmer/in');
                            }
                        } else if ($course['status'] == 'user') {
                            switch ($user->geschlecht) {
                                case 1:
                                    $status = dgettext('whowaswhere', 'Leser');
                                    break;
                                case 2:
                                    $status = dgettext('whowaswhere', 'Leserin');
                                    break;
                                default:
                                    $status = dgettext('whowaswhere', 'Leser/in');
                            }
                        } else if ($course['status'] == 'awaiting') {
                            $status = dgettext('whowaswhere', 'Warteliste');
                        } else if ($course['status'] == 'accepted') {
                            $status = dgettext('whowaswhere', 'Vorläufig akzeptiert');
                        } else {
                            $status = dgettext('whowaswhere', 'unbekannt');
                        }
                        ?>
                        <tr>
                            <td><?= htmlReady($course['VeranstaltungsNummer']) ?></td>
                            <td>
                                <a href="<?= URLHelper::getLink('dispatch.php/course/details', ['cid' => $course['Seminar_id']]) ?>">
                                    <?= htmlReady($course['Name']) ?></a>
                            </td>
                            <td><?= htmlReady($course['type']) ?></td>
                            <td><?= htmlReady($status) ?></td>
                            <td><?= date('d.m.Y H:i', $course['mkdate']) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php endforeach ?>
        <?php else : ?>
            <?= MessageBox::info(
                dgettext('whowaswhere', 'Es wurden keine Veranstaltungen gefunden.')) ?>
        <?php endif ?>
    </section>
</article>
