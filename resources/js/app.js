// resources/js/app.js
import './bootstrap'

// === Alpine.js + plugin focus (para x-trap.noscroll) ===
import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'

Alpine.plugin(focus)
window.Alpine = Alpine
Alpine.start()

// (opcional) visible para debug:
console.log('Alpine:', Alpine.version)