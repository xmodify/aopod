/**
 * Flatpickr Thai Buddhist Era (พ.ศ.) Plugin & Helper
 * Automatically handles Thai language, Buddhist year (+543), and clean AltInput display.
 */
(function (global) {
  'use strict';

  function initThaiDatePicker(selector, options) {
    options = options || {};

    const thaiLocale = (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.th) ? flatpickr.l10ns.th : {
      firstDayOfWeek: 0,
      weekdays: {
        shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
        longhand: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์']
      },
      months: {
        shorthand: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
        longhand: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม']
      }
    };

    function updateYearDisplay(fp) {
      setTimeout(function () {
        if (!fp || !fp.calendarContainer) return;
        const curYearInput = fp.calendarContainer.querySelector('.cur-year');
        if (curYearInput) {
          const beYear = fp.currentYear + 543;
          curYearInput.value = beYear;
        }
      }, 0);
    }

    const defaultOptions = {
      locale: thaiLocale,
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'j M Y',
      formatDate: function (date, format, locale) {
        const d = date.getDate();
        const monthIdx = date.getMonth();
        const beYear = date.getFullYear() + 543;

        if (format === 'j M Y') {
          const m = (locale && locale.months && locale.months.shorthand) ? locale.months.shorthand[monthIdx] : (monthIdx + 1);
          return d + ' ' + m + ' ' + beYear;
        }
        if (format === 'd/m/Y') {
          const dStr = String(d).padStart(2, '0');
          const mStr = String(monthIdx + 1).padStart(2, '0');
          return dStr + '/' + mStr + '/' + beYear;
        }
        return flatpickr.formatDate(date, format, locale);
      },
      onReady: function (selectedDates, dateStr, instance) {
        updateYearDisplay(instance);
        if (instance.altInput) {
          instance.altInput.classList.add('form-control');
        }
      },
      onOpen: function (selectedDates, dateStr, instance) {
        updateYearDisplay(instance);
      },
      onMonthChange: function (selectedDates, dateStr, instance) {
        updateYearDisplay(instance);
      },
      onYearChange: function (selectedDates, dateStr, instance) {
        updateYearDisplay(instance);
      },
      onValueUpdate: function (selectedDates, dateStr, instance) {
        updateYearDisplay(instance);
      }
    };

    const config = Object.assign({}, defaultOptions, options);
    return flatpickr(selector, config);
  }

  global.initThaiDatePicker = initThaiDatePicker;
})(typeof window !== 'undefined' ? window : this);
