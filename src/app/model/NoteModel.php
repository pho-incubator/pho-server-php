<?php

/**
 * NoteModel
 * This is basically a simple CRUD (Create/Read/Update/Delete) demonstration.
 */
class NoteModel
{
    /**
     * Get all notes (notes are just example data that the user has created)
     * @return array|\model\DynamoDb\Note[] an array with several objects (the results)
     */
    public static function getAllNotes()
    {
        return \Kettle\ORM::factory(model\DynamoDb\Note::class)->findAll();
    }

    /**
     * Get a single note
     * @param int $note_id id of the specific note
     * @return object a single object (the result)
     */
    public static function getNote($note_id)
    {
        /** @var \model\DynamoDb\Note $note */
        $note = \Kettle\ORM::factory(model\DynamoDb\Note::class)->findOne($note_id);

        if(
            is_null($note)
            || $note->user_id !== Session::get('user_id')
        ) {
            return false;
        }

        return $note;
    }

    /**
     * Set a note (create a new one)
     * @param string $note_text note text that will be created
     * @return bool feedback (was the note created properly ?)
     */
    public static function createNote($note_text)
    {
        if (!$note_text || strlen($note_text) == 0) {
            Session::add('feedback_negative', Text::get('FEEDBACK_NOTE_CREATION_FAILED'));
            return false;
        }

        /** @var \model\DynamoDb\Note $note */
        $note = \Kettle\ORM::factory(model\DynamoDb\Note::class)->create();
        $note->note_text = $note_text;
        $note->user_id = Session::get('user_id');

        if ($note->save()) {
            return true;
        }

        // default return
        Session::add('feedback_negative', Text::get('FEEDBACK_NOTE_CREATION_FAILED'));
        return false;
    }

    /**
     * Update an existing note
     * @param int $note_id id of the specific note
     * @param string $note_text new text of the specific note
     * @return bool feedback (was the update successful ?)
     */
    public static function updateNote($note_id, $note_text)
    {
        if (!$note_id || !$note_text) {
            return false;
        }

        /** @var \model\DynamoDb\Note $note */
        $note = \Kettle\ORM::factory(model\DynamoDb\Note::class)->findOne($note_id);
        if(is_null($note)) {
            return false;
        }
        $note->note_text = $note_text;

        if ($note->save()) {
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_NOTE_EDITING_FAILED'));
        return false;
    }

    /**
     * Delete a specific note
     * @param int $note_id id of the note
     * @return bool feedback (was the note deleted properly ?)
     */
    public static function deleteNote($note_id)
    {
        if (!$note_id) {
            return false;
        }

        /** @var \model\DynamoDb\Note $note */
        $note = \Kettle\ORM::factory(model\DynamoDb\Note::class)->findOne($note_id);

        if (is_null($note)) {
            return false;
        }

        if ($note->delete()) {
            return true;
        }

        // default return
        Session::add('feedback_negative', Text::get('FEEDBACK_NOTE_DELETION_FAILED'));
        return false;
    }
}
