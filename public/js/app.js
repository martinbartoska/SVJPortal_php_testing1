// Light Portal - Main Application JS with Alpine.js integration

const API = {
    baseURL: '/api',

    async request(endpoint, options = {}) {
        const response = await fetch(`${this.baseURL}${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }

        return await response.json();
    },

    // Dashboard
    getDashboard() {
        return this.request('/dashboard');
    },

    // Surveys
    getSurveys() {
        return this.request('/surveys');
    },

    getSurvey(id) {
        return this.request(`/surveys/${id}`);
    },

    createSurvey(data) {
        return this.request('/surveys', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    updateSurvey(id, data) {
        return this.request(`/surveys/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    submitSurveyResponse(surveyId, responses) {
        return this.request(`/surveys/${surveyId}/responses`, {
            method: 'POST',
            body: JSON.stringify(responses)
        });
    },

    // Quizzes
    getQuizzes() {
        return this.request('/quizzes');
    },

    getQuiz(id) {
        return this.request(`/quizzes/${id}`);
    },

    createQuiz(data) {
        return this.request('/quizzes', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    updateQuiz(id, data) {
        return this.request(`/quizzes/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    startQuizAttempt(quizId) {
        return this.request(`/quizzes/${quizId}/attempt`, {
            method: 'POST'
        });
    },

    submitQuizAnswer(attemptId, questionId, answer) {
        return this.request(`/quizzes/attempt/${attemptId}`, {
            method: 'POST',
            body: JSON.stringify({ questionId, answer })
        });
    },

    completeQuizAttempt(attemptId) {
        return this.request(`/quizzes/attempt/${attemptId}/complete`, {
            method: 'POST'
        });
    },

    getQuizResults(attemptId) {
        return this.request(`/quizzes/attempt/${attemptId}/results`);
    }
};

// Utility Functions
const Utils = {
    formatDate(date) {
        if (!date) return '';
        const d = new Date(date);
        return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
    },

    showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        document.body.insertBefore(alertDiv, document.body.firstChild);

        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    },

    openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('open');
        }
    },

    closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('open');
        }
    }
};

// Alpine.js initialization
document.addEventListener('DOMContentLoaded', function() {
    // Add any global Alpine.js data or methods
    console.log('Portal initialized');
});
