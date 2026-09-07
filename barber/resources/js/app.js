import Alpine from 'alpinejs';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import html2canvas from 'html2canvas';

window.Alpine = Alpine;
window.FullCalendar = { Calendar, dayGridPlugin, timeGridPlugin, interactionPlugin };
window.html2canvas = html2canvas;

Alpine.start();
