// Import Alpine.js and start it
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Import axios and set up headers
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Laravel Echo and Pusher
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

    // Participant Status Updated Listener
    window.Echo.channel(`room.${roomId}`)
        .listen('.ParticipantStatusUpdated', (e) => {
            Livewire.emit('refreshRoom');
            console.log('Participant status updated for room:', e.roomId);
        });

    // Detect participant disconnect on page unload (e.g., closing tab, refreshing, or navigating away)
    window.addEventListener('beforeunload', function () {
        // Check if the user is a participant (not admin) and has an ID
        if (window.participantId && !window.isAdmin) {
            // Use the Beacon API to notify the server
            const disconnectUrl = `/room/${roomId}/participant-disconnect`;
            const data = { participant_id: window.participantId };

            // Send data to the server before the page unloads
            if (navigator.sendBeacon) {
                const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
                navigator.sendBeacon(disconnectUrl, blob);
            } else {
                // Fallback to fetch if Beacon API is unavailable
                fetch(disconnectUrl, {
                    method: 'POST',
                    body: JSON.stringify(data),
                    headers: { 'Content-Type': 'application/json' }
                }).catch(error => console.error("Error notifying participant disconnect:", error));
            }
        }
    });

document.addEventListener('DOMContentLoaded', () => {
    console.log("Setting up Echo listener for question updates...");

    const roomId = window.roomId;
    console.log(`Subscribing to Echo channel: room.${roomId}`);

    let lastQuestionId = null; // Track the last question ID displayed
    let timerInterval = null; // Reference for the timer interval
    let hasAnswered = false; // Flag to track submission state
    let currentLeaderboard = []; // Store the leaderboard in memory

    try {
        // Listening for QuestionUpdated events
        window.Echo.channel(`room.${roomId}`)
            .listen('.QuestionUpdated', (e) => {
                console.log("QuestionUpdated event received:", e);

                // Reset hasAnswered flag for each new question
                if (e.questionId !== lastQuestionId) {
                    hasAnswered = false;
                }

                // Update leaderboard if present in the QuestionUpdated event
                if (e.leaderboard && Array.isArray(e.leaderboard)) {
                    updateLeaderboard(e.leaderboard);
                } else {
                    updateLeaderboard(currentLeaderboard); // Persist last known leaderboard if not provided
                }

                lastQuestionId = e.questionId;

                const {
                    quizStarted,
                    questionId,
                    questionText = "Question text not provided.",
                    questionType,
                    options = [],
                    timer, // Destructure timer value
                    currentDifficulty // Added currentDifficulty
                } = e;

                const waitingMessageEl = document.getElementById('waitingMessage');
                const countdownDisplayEl = document.getElementById('countdownDisplay');
                const questionDisplayEl = document.getElementById('questionDisplay');
                const optionsContainerEl = document.getElementById('optionsContainer');
                const difficultyDisplayEl = document.getElementById('difficultyDisplay'); // Added difficulty display element
                let answersContainer = window.isAdmin ? document.getElementById('answersContainer') : null;


                if (difficultyDisplay) {
                    difficultyDisplay.innerText = `Round: ${e.currentDifficulty}`;
                    switch (e.currentDifficulty) {
                        case 'Easy':
                            difficultyDisplay.style.color = '#4CAF50'; // Light green for easy
                            break;
                        case 'Average':
                            difficultyDisplay.style.color = '#FFD700'; // Yellow for average
                            break;
                        case 'Difficult':
                            difficultyDisplay.style.color = '#FF6347'; // Red for difficult
                            break;
                        case 'Clincher':
                            difficultyDisplay.style.color = '#696969'; // Dark gray for clincher
                            break;
                        default:
                            difficultyDisplay.style.color = '#000000'; // Default to black
                    }
                }

                if (!waitingMessageEl || !countdownDisplayEl || !questionDisplayEl || !optionsContainerEl || !difficultyDisplayEl || (window.isAdmin && !answersContainer)) {
                    console.error("One or more essential elements are missing from the DOM.");
                    return;
                }

                resetUI(waitingMessageEl, countdownDisplayEl, questionDisplayEl, optionsContainerEl, difficultyDisplayEl);

                if (answersContainer) {
                    answersContainer.innerHTML = '<li class="text-gray-500">No answers submitted yet.</li>'; // Reset answers container
                }

                if (quizStarted) {
                    waitingMessageEl.classList.add('hidden');
                    countdownDisplayEl.classList.remove('hidden');
                    countdownDisplayEl.style.color = 'green';
                    countdownDisplayEl.innerText = "Starting in 3 seconds...";

                    // Set difficulty level
                    difficultyDisplayEl.classList.remove('hidden');
                    difficultyDisplayEl.innerText = `Difficulty: ${currentDifficulty || 'Unknown'}`;

                    let countdown = 3;
                    const initialCountdownInterval = setInterval(() => {
                        countdownDisplayEl.innerText = `Starting in ${countdown} seconds...`;
                        countdown--;

                        if (countdown < 0) {
                            clearInterval(initialCountdownInterval);
                            countdownDisplayEl.classList.add('hidden');
                            questionDisplayEl.classList.remove('hidden');
                            questionDisplayEl.innerText = questionText;

                            if (optionsContainerEl && options.length > 0) {
                                optionsContainerEl.classList.remove('hidden');
                                optionsContainerEl.innerHTML = generateInputFieldHTML(questionType, options);

                                const submitButtonEl = createSubmitButton(questionId);
                                optionsContainerEl.appendChild(submitButtonEl);

                                optionsContainerEl.addEventListener('input', () => {
                                    enableSubmitButton(submitButtonEl);
                                });

                                submitButtonEl.addEventListener('click', () => {
                                    handleSubmitAnswer(submitButtonEl, questionId, answersContainer);
                                });
                            }

                            if (window.isAdmin && e.submittedAnswers.length > 0) {
                                updateParticipantsAnswers(e.submittedAnswers);
                            }

                            // Start the timer for the question's difficulty type after the initial countdown
                            if (timer) {
                                startTimer(timer, questionId);
                            }
                        }
                    }, 1000);
                }
            });

        if (window.isAdmin) {
            console.log("Setting up listener for AnswerSubmitted events.");
            window.Echo.channel(`room.${roomId}`)
            .listen('.AnswerSubmitted', (e) => {
                console.log("AnswerSubmitted event received.");

                // Update the leaderboard with new data if it’s an array and not empty
                if (Array.isArray(e.leaderboard) && e.leaderboard.length > 0) {
                    updateLeaderboard(e.leaderboard);
                }

                // Update the participant answers if submittedAnswers data is present and is an array
                if (Array.isArray(e.submittedAnswers)) {
                    updateParticipantsAnswers(e.submittedAnswers);
                } else {
                    console.warn('Submitted answers data is not an array or is undefined.');
                    updateParticipantsAnswers([]); // Clear the container
                }
            });
        }

    } catch (error) {
        console.error("Failed to set up Echo listener:", error);
    }

    function resetUI(waitingEl, countdownEl, questionEl, optionsEl, difficultyEl) {
        waitingEl.classList.remove('hidden');
        countdownEl.classList.add('hidden');
        questionEl.classList.add('hidden');
        optionsEl.classList.add('hidden');
        difficultyEl.classList.add('hidden'); // Hide difficulty level initially
        optionsEl.innerHTML = '';
        clearInterval(timerInterval); // Clear any existing timer interval
        timerInterval = null; // Reset timer interval reference
    }

    function generateInputFieldHTML(type, options) {
        if (type === 'Identification') {
            return `<input type="text" id="participantAnswer" class="border p-1 w-full" placeholder="Type your answer here" />`;
        } else if (type === 'True or False') {
            return `
                <label class="block"><input type="radio" name="participantAnswer" value="True" class="mr-2" /> True</label>
                <label class="block"><input type="radio" name="participantAnswer" value="False" class="mr-2" /> False</label>
            `;
        } else if (type === 'Multiple Choice') {
            if (Array.isArray(options) && options.length > 0 && typeof options[0] === 'object') {
                return options.map(option => `
                    <label class="block">
                        <input type="radio" name="participantAnswer" value="${option.text}" class="mr-2" />
                        ${option.label}. ${option.text}
                    </label>
                `).join('');
            } else {
                return options.map((option, index) => `
                    <label class="block">
                        <input type="radio" name="participantAnswer" value="${option}" class="mr-2" />
                        ${String.fromCharCode(65 + index)}. ${option}
                    </label>
                `).join('');
            }
        }
        return '';
    }

    function createSubmitButton(questionId) {
        const button = document.createElement('button');
        button.id = 'submitAnswer';
        button.className = 'mt-4 bg-blue-500 text-white px-4 py-2 rounded disabled:opacity-50';
        button.textContent = 'Submit Answer';
        button.disabled = true;
        return button;
    }

    function enableSubmitButton(button) {
        const answerInput = document.getElementsByName('participantAnswer');
        button.disabled = !(
            Array.from(answerInput).some(input => input.checked) ||
            (document.getElementById('participantAnswer')?.value.trim() || '').length > 0
        );
    }

    function handleSubmitAnswer(button, questionId, answersContainer) {
        const answerInputs = document.getElementsByName('participantAnswer');
        let participantAnswer = null;

        if (answerInputs.length) {
            const selectedInput = Array.from(answerInputs).find(input => input.checked);
            participantAnswer = selectedInput ? selectedInput.value : null;
        } else {
            const textAnswer = document.getElementById('participantAnswer');
            if (textAnswer) {
                participantAnswer = textAnswer.value.trim();
                textAnswer.disabled = true;
            }
        }

        hasAnswered = true;

        Array.from(answerInputs).forEach(input => input.disabled = true);
        if (button) button.disabled = true;

        Livewire.emit('submitAnswer', participantAnswer, questionId);
        console.log("Emitting submitAnswer with questionId:", questionId, "and participantAnswer:", participantAnswer || 'No Answer');

        axios.post(`/room/${roomId}/submit-answer-notification`, {
            questionId: questionId,
            participantAnswer: participantAnswer || 'No Answer'
        }).then(response => {
            console.log("Notification sent:", response.data);
        }).catch(error => {
            console.error("Error sending notification:", error);
        });
    }

    function startTimer(duration, questionId) {
        let timer = duration;
        const countdownEl = document.getElementById('countdownDisplay');
        countdownEl.classList.remove('hidden');

        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            countdownEl.innerText = `Time remaining: ${timer} seconds`;
            timer--;

            if (timer < 0) {
                clearInterval(timerInterval);
                countdownEl.classList.add('hidden');
                console.log(`Time's up for questionId: ${questionId}`);

                if (!hasAnswered) {
                    console.log('Automatically submitting as "No Answer" due to time running out.');
                    handleSubmitAnswer(null, questionId, null);
                }
            }
        }, 1000);
    }

    function updateLeaderboard(leaderboardData) {
        currentLeaderboard = leaderboardData.length > 0 ? leaderboardData : currentLeaderboard;

        const leaderboardContainer = document.getElementById('leaderboardContainer');
        if (!leaderboardContainer) {
            console.error('Leaderboard container not found.');
            return;
        }

        const leaderboardBody = leaderboardContainer.querySelector('tbody');
        if (!leaderboardBody) {
            console.error('Leaderboard body not found.');
            return;
        }
        leaderboardBody.innerHTML = '';
        if (currentLeaderboard.length > 0) {
            currentLeaderboard.forEach((entry, index) => {
                const tr = document.createElement('tr');
                tr.className = index % 2 === 0 ? 'bg-gray-100' : '';
                tr.innerHTML = `<td>${index + 1}</td><td>${entry.name}</td><td>${entry.score}</td>`;
                leaderboardBody.appendChild(tr);
            });
        } else {
            const noDataTr = document.createElement('tr');
            noDataTr.innerHTML = `<td colspan="3" class="text-center text-gray-500">No data available.</td>`;
            leaderboardBody.appendChild(noDataTr);
        }

        console.log('Leaderboard updated in the UI with:', currentLeaderboard);
    }

    function updateParticipantsAnswers(submittedAnswers) {
        const answersContainer = document.getElementById('answersContainer');
        if (!answersContainer) {
            console.error('Answers container not found.');
            return;
        }

        answersContainer.innerHTML = '';

        if (submittedAnswers.length === 0) {
            const noAnswersRow = document.createElement('tr');
            const noAnswersCell = document.createElement('td');
            noAnswersCell.colSpan = 3;
            noAnswersCell.classList.add('text-center', 'text-gray-500');
            noAnswersCell.textContent = 'No answers submitted yet.';
            noAnswersRow.appendChild(noAnswersCell);
            answersContainer.appendChild(noAnswersRow);
            return;
        }

        submittedAnswers.forEach(answer => {
            const row = document.createElement('tr');

            // Username cell
            const usernameCell = document.createElement('td');
            usernameCell.classList.add('px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'font-medium', 'text-gray-900');
            usernameCell.textContent = answer.participant.name ?? 'Unknown';
            row.appendChild(usernameCell);

            // Answer cell
            const answerCell = document.createElement('td');
            answerCell.classList.add('px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'text-gray-500');
            answerCell.textContent = answer.sub_answer;
            row.appendChild(answerCell);

            // Result cell
            const resultCell = document.createElement('td');
            resultCell.classList.add('px-6', 'py-4', 'whitespace-nowrap', 'text-sm', answer.correct ? 'text-green-600' : 'text-red-600');
            resultCell.textContent = answer.correct ? 'Correct' : 'Incorrect';
            row.appendChild(resultCell);

            answersContainer.appendChild(row);
        });

        console.log("Participants' answers updated in the UI with:", submittedAnswers);
    }

});
