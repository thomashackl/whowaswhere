<? if(!empty($studycourses)) : ?>
    <ul>
    <? foreach ($studycourses as $s) : ?>
        <li>
            <?= htmlReady($s->degree_name) ?>
            <?= htmlReady($s->studycourse_name) ?>
            (<?= htmlReady($s->semester) ?>)
        </li>
    <? endforeach ?>
    </ul>
<? endif ?>
