<?php

namespace App\Http\Livewire\Participant;

use App\Models\Participant;
use App\Models\Quiz;
use Livewire\Component;

class ParticipantForm extends Component
{
    public Quiz $quiz;
    public $participant = [
        'name' => '',
        'code' => ''
    ];
    public bool $editing = false;
    public $selectedParticipantId = null;
    public $participantsList = [];
    public $showDeleteModal = false; // Control for delete modal
    public bool $showSaveModal = false; // Control for save modal

    protected function rules()
    {
        $codeRule = $this->selectedParticipantId
            ? 'required|string|max:255|unique:participants,code,' . $this->selectedParticipantId
            : 'required|string|max:255|unique:participants,code';

        return [
            'participant.name' => 'required|string|max:255',
            'participant.code' => $codeRule,
        ];
    }

    public function mount(Quiz $quiz, $participantId = null)
    {
        $this->quiz = $quiz;
        $this->participantsList = $quiz->participants()->get()->toArray();

        if ($participantId) {
            $this->editing = true;
            $this->selectedParticipantId = $participantId;
            $participant = Participant::findOrFail($participantId);
            $this->participant = [
                'name' => $participant->name,
                'code' => $participant->code,
            ];
        } else {
            $this->generateCode(); // Auto-generate code on initialization for a new participant
        }
    }

    public function generateCode()
    {
        $this->participant['code'] = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8));
    }

    public function addParticipant()
    {
        $this->validate();

        $this->participant['quiz_id'] = $this->quiz->id;
        Participant::create($this->participant);

        // Reset the form for a new participant and update the list
        $this->reset('participant');
        $this->generateCode();
        $this->participantsList = $this->quiz->participants()->get()->toArray();

        session()->flash('success', 'Participant added successfully.');
    }

    public function selectParticipant($participantId)
    {
        $this->selectedParticipantId = $participantId;
        $this->editing = true;

        $participant = Participant::findOrFail($participantId);
        $this->participant = [
            'name' => $participant->name,
            'code' => $participant->code,
        ];
    }

    public function saveParticipants()
    {
        $this->participantsList = $this->quiz->participants()->get()->toArray();

        session()->flash('success', 'All participants saved successfully.');

        // Redirect to the quiz edit page
        return redirect()->route('quiz.edit', ['quiz' => $this->quiz->slug]);
    }

    public function save()
    {
        $this->validate();

        $this->participant['quiz_id'] = $this->quiz->id;

        if ($this->editing && $this->selectedParticipantId) {
            $existingParticipant = Participant::find($this->selectedParticipantId);
            if ($existingParticipant) {
                $existingParticipant->update($this->participant);
            }
        } else {
            Participant::create($this->participant);
        }

        // Reset the form and refresh the participants list
        $this->reset('participant');
        $this->generateCode();
        $this->participantsList = $this->quiz->participants()->get()->toArray();
        $this->editing = false;

        session()->flash('success', 'Participant saved successfully.');
    }

    public function confirmRemoveParticipant($participantId)
    {
        $this->selectedParticipantId = $participantId;
        $this->showDeleteModal = true; // Show delete confirmation modal
    }
    public function confirmSave(): void
    {
        $this->showSaveModal = true; // Display the save confirmation modal
    }
    public function saveParticipantsAndRedirect(): void
    {
        // Save any necessary data before redirecting, but without triggering validation
        $this->showSaveModal = false; // Close the modal

        // Redirect to the quiz edit page using the slug
        redirect()->route('quiz.edit', ['quiz' => $this->quiz->slug])
            ->with('success', 'Participants saved successfully and redirected.');
    }

    public function removeParticipant()
    {
        $participant = Participant::findOrFail($this->selectedParticipantId);

        if ($participant) {
            $participant->delete(); // Soft delete the participant
            $this->participantsList = $this->quiz->participants()->get()->toArray();
            session()->flash('success', 'Participant removed successfully.');
        } else {
            session()->flash('error', 'Participant not found.');
        }

        $this->showDeleteModal = false; // Hide delete confirmation modal
        $this->selectedParticipantId = null;
    }

    public function render()
    {
        return view('livewire.participant.participant-form', [
            'participantsList' => $this->participantsList,
        ]);
    }
}
