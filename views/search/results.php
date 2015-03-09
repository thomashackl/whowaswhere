<?php
if ($message) {
    echo $message;
}
?>
<?php if ($courses) { ?>
    <h1><?= sprintf(dgettext('whowaswhere', 'Veranstaltungen, an denen %s teilgenommen hat'), $user->getFullname()) ?></h1>
    <?php foreach ($courses as $semester => $entries) { ?>
<table class="default">
    <caption><?= htmlReady($semester) ?></caption>
    <thead>
        <tr>
            <th width="10%"><?= dgettext('whowaswhere', 'Nummer') ?></th>
            <th><?= dgettext('whowaswhere', 'Titel') ?></th>
            <th width="15%"><?= dgettext('whowaswhere', 'Typ') ?></th>
            <th width="15%"><?= dgettext('whowaswhere', 'Status') ?></th>
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
        } else if (in_array($course['status'], array('user', 'autor'))) {
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
        } else {
            $status = dgettext('whowaswhere', 'unbekannt');
        }
    ?>
        <tr>
            <td><?= htmlReady($course['VeranstaltungsNummer']) ?></td>
            <td><?= htmlReady($course['Name']) ?></td>
            <td><?= htmlReady($course['type']) ?></td>
            <td><?= htmlReady($status) ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
    <?php } ?>
<?php } ?>