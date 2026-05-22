import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useGameStore = defineStore('game', () => {
  // État réactif
  const gameId = ref(null);
  const isPaused = ref(false);
  const remainingSeconds = ref(0);
  const isTimerActive = ref(false);
  const pausedAt = ref(null); // Timestamp when the game was paused
  let timerInterval = null;

  // Récupérer les données du localStorage
  const loadFromStorage = () => {
    const saved = localStorage.getItem('cityplay_game');
    if (saved) {
      try {
        const data = JSON.parse(saved);
        gameId.value = data.gameId;
        isPaused.value = data.isPaused;
        remainingSeconds.value = data.remainingSeconds;
        isTimerActive.value = data.isTimerActive;
        pausedAt.value = data.pausedAt;
        
        // If the game was paused, calculate how much time has passed and resume automatically
        if (isPaused.value && pausedAt.value) {
          const now = Date.now();
          const pausedDuration = Math.floor((now - pausedAt.value) / 1000);
          if (pausedDuration > 0) {
            remainingSeconds.value = Math.max(0, remainingSeconds.value - pausedDuration);
            isPaused.value = false;
            pausedAt.value = null;
          }
        }
      } catch (e) {
        console.error('Erreur lors du chargement du jeu depuis localStorage:', e);
      }
    }
  };

  // Sauvegarder dans le localStorage
  const saveToStorage = () => {
    const data = {
      gameId: gameId.value,
      isPaused: isPaused.value,
      remainingSeconds: remainingSeconds.value,
      isTimerActive: isTimerActive.value,
      pausedAt: pausedAt.value,
    };
    localStorage.setItem('cityplay_game', JSON.stringify(data));
  };

  // Initialiser le jeu
  const initializeGame = (id, durationMinutes) => {
    gameId.value = id;
    remainingSeconds.value = durationMinutes * 60;
    isPaused.value = false;
    pausedAt.value = null;
    isTimerActive.value = true;
    saveToStorage();
    startTimer();
  };

  // Lancer le chronomètre
  const startTimer = () => {
    if (timerInterval) clearInterval(timerInterval);
    isTimerActive.value = true;
    
    timerInterval = setInterval(() => {
      if (!isPaused.value && remainingSeconds.value > 0) {
        remainingSeconds.value--;
        saveToStorage();
        
        if (remainingSeconds.value <= 0) {
          stopTimer();
          // Événement quand le temps est écoulé
          window.dispatchEvent(new CustomEvent('game-time-up'));
        }
      }
    }, 1000);
  };

  // Mettre en pause
  const pause = () => {
    isPaused.value = true;
    pausedAt.value = Date.now();
    saveToStorage();
  };

  // Reprendre
  const resume = () => {
    if (isPaused.value && pausedAt.value) {
      const now = Date.now();
      const pausedDuration = Math.floor((now - pausedAt.value) / 1000);
      if (pausedDuration > 0) {
        remainingSeconds.value = Math.max(0, remainingSeconds.value - pausedDuration);
      }
    }
    isPaused.value = false;
    pausedAt.value = null;
    saveToStorage();
  };

  // Arrêter le timer
  const stopTimer = () => {
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }
    isTimerActive.value = false;
    saveToStorage();
  };

  // Réinitialiser
  const reset = () => {
    stopTimer();
    gameId.value = null;
    isPaused.value = false;
    remainingSeconds.value = 0;
    isTimerActive.value = false;
    pausedAt.value = null;
    localStorage.removeItem('cityplay_game');
  };

  // Formatage du temps pour affichage
  const formattedTime = computed(() => {
    const minutes = Math.floor(remainingSeconds.value / 60);
    const seconds = remainingSeconds.value % 60;
    return `${minutes} min ${seconds.toString().padStart(2, '0')}s`;
  });

  // Charger les données au chargement du store
  loadFromStorage();

  return {
    // État
    gameId,
    isPaused,
    remainingSeconds,
    isTimerActive,
    pausedAt,
    // Computed
    formattedTime,
    // Méthodes
    initializeGame,
    startTimer,
    pause,
    resume,
    stopTimer,
    reset,
    loadFromStorage,
  };
});
