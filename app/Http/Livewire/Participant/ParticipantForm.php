<?php

namespace App\Http\Livewire\Participant;

use App\Models\Participant;
use App\Models\Quiz;
use Livewire\Component;

class ParticipantForm extends Component
{
    public Quiz $quiz;
    public array $participants = [];
    public bool $editing = false;

    protected function rules()
    {
        $rules = [
            'participants.*.name' => 'required|string|max:255',
            'participants.*.code' => 'required|string|max:255',
        ];

        // Unique validation for participants' code based on their existence
        foreach ($this->participants as $index => $participant) {
            if (!empty($participant['id'])) {
                $rules["participants.{$index}.code"] = 'required|string|max:255|unique:participants,code,' . $participant['id'];
            } else {
                $rules["participants.{$index}.code"] = 'required|string|max:255|unique:participants,code';
            }
        }

        return $rules;
    }

    public function mount(Quiz $quiz, $participantId = null)
    {
        $this->quiz = $quiz;
        if ($participantId) {
            $this->editing = true;
            $participant = Participant::findOrFail($participantId);
            $this->participants[] = [
                'name' => $participant->name,
                'code' => $participant->code,
                'id' => $participant->id,
            ];
        } else {
            $this->participants = $quiz->participants()->get()->toArray();

            if (empty($this->participants)) {
                $this->addParticipant();
            }
        }
    }

    public function addParticipant()
    {
        $this->participants[] = ['name' => '', 'code' => ''];
    }

    public function generateCode($index)
    {
        $this->participants[$index]['code'] = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8));
    }

    public function removeParticipant($index)
    {
        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);
    }

    public function save()
    {
        $this->validate();

        // Get all participant IDs already in the quiz
        $existingParticipantIds = Participant::where('quiz_id', $this->quiz->id)->pluck('id')->toArray();
        $currentParticipantIds = [];

        // Loop through participants and update/create
        foreach ($this->participants as $participantData) {
            $participantData['quiz_id'] = $this->quiz->id;

            if (isset($participantData['id'])) {
                $existingParticipant = Participant::find($participantData['id']);
                if ($existingParticipant) {
                    $existingParticipant->update($participantData);
                    $currentParticipantIds[] = $existingParticipant->id;
                }
            } else {
                $newParticipant = Participant::create($participantData);
                $currentParticipantIds[] = $newParticipant->id;
            }
        }

        // Delete participants that were removed from the form
        $participantsToDelete = array_diff($existingParticipantIds, $currentParticipantIds);
        Participant::destroy($participantsToDelete);

        return redirect()->route('quiz.edit', ['quiz' => $this->quiz->slug])->with('success', 'Participants saved successfully.');
    }

    public function render()
    {
        return view('livewire.participant.participant-form');
    }
}
