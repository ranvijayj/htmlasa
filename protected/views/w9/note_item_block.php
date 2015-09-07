<div class="note_item">
    <p class="note_date"><?php echo Helper::convertDateString($note->Created); ?></p>
    <p class="note_title"><?php echo CHtml::encode($user->person->First_Name) . ' ' . CHtml::encode($user->person->Last_Name); ?></p>
    <p class="note_body"><?php echo CHtml::encode($note->Comment); ?></p>
</div>