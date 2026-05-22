import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAudioStore = defineStore('audio', () => {
  // État
  const enabled = ref(true);
  const volume = ref(0.3); // Volume par défaut (0 à 1)

  // Charger les paramètres depuis localStorage
  const loadFromStorage = () => {
    const saved = localStorage.getItem('cityplay_audio');
    if (saved) {
      try {
        const data = JSON.parse(saved);
        enabled.value = data.enabled ?? true;
        volume.value = data.volume ?? 0.3;
      } catch (e) {
        console.error('Erreur lors du chargement des paramètres audio:', e);
      }
    }
  };

  // Sauvegarder les paramètres
  const saveToStorage = () => {
    localStorage.setItem('cityplay_audio', JSON.stringify({
      enabled: enabled.value,
      volume: volume.value,
    }));
  };

  // Basculer l'audio
  const toggle = () => {
    enabled.value = !enabled.value;
    saveToStorage();
  };

  // Mettre à jour le volume
  const setVolume = (vol) => {
    volume.value = Math.max(0, Math.min(1, vol));
    saveToStorage();
  };

  // Jouer un son (utilise des URL de sons ou des Web Audio API)
  // Note: Pour l'instant, on utilise des sons génériques, vous pourrez remplacer par vos propres fichiers
  const play = (type) => {
    if (!enabled.value) return;

    // Ici, vous pouvez ajouter des fichiers audio dans public/audio/
    // Pour l'exemple, on utilise l'API Web Audio pour créer des sons simples
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    gainNode.gain.value = volume.value;

    switch (type) {
      case 'success':
        // Son de succès
        oscillator.frequency.value = 523.25; // Do
        oscillator.type = 'sine';
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.5);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
        break;
      case 'error':
        // Son d'erreur
        oscillator.frequency.value = 200;
        oscillator.type = 'sawtooth';
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.3);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
        break;
      case 'notification':
        // Son de notification
        oscillator.frequency.value = 880;
        oscillator.type = 'sine';
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.2);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
        break;
      case 'click':
        // Son de clic
        oscillator.frequency.value = 440;
        oscillator.type = 'square';
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.1);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
        break;
      default:
        oscillator.frequency.value = 440;
        oscillator.type = 'sine';
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.3);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    }
  };

  loadFromStorage();

  return {
    enabled,
    volume,
    toggle,
    setVolume,
    play,
  };
});
