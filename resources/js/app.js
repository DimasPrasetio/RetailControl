import './bootstrap';
import CustomSelect from './custom-select';

// Expose globally so blade scripts can call: new CustomSelect(el)
globalThis.CustomSelect = CustomSelect;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tom-select-init').forEach(el => new CustomSelect(el));
});
