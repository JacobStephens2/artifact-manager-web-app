(function () {
  'use strict';

  if (!window.Capacitor || typeof window.Capacitor.isNativePlatform !== 'function' || !window.Capacitor.isNativePlatform()) {
    return;
  }

  var LocalNotifications = window.Capacitor.Plugins && window.Capacitor.Plugins.LocalNotifications;
  if (!LocalNotifications) return;

  var API_URL = 'https://api.artifact.stephens.page/upcoming-interactions.php';
  var NOTIFICATION_HOUR = 9;
  var SOON_LEAD_DAYS = 3;
  var PAST_DUE_OFFSET_DAYS = 1;

  function atNineAm(dateString, offsetDays) {
    var parts = dateString.split('-');
    var d = new Date(
      parseInt(parts[0], 10),
      parseInt(parts[1], 10) - 1,
      parseInt(parts[2], 10),
      NOTIFICATION_HOUR, 0, 0, 0
    );
    if (offsetDays) d.setDate(d.getDate() + offsetDays);
    return d;
  }

  function nineAmTomorrow() {
    var now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, NOTIFICATION_HOUR, 0, 0, 0);
  }

  function buildNotifications(items, now) {
    var out = [];
    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      if (!item || !item.id || !item.use_by_date || !item.title) continue;

      var soon = atNineAm(item.use_by_date, -SOON_LEAD_DAYS);
      var due = atNineAm(item.use_by_date, 0);
      var past = atNineAm(item.use_by_date, PAST_DUE_OFFSET_DAYS);

      if (soon > now) {
        out.push({
          id: item.id * 10 + 1,
          title: 'Due soon',
          body: item.title + ' is due ' + item.use_by_date,
          schedule: { at: soon, allowWhileIdle: true }
        });
      }
      if (due > now) {
        out.push({
          id: item.id * 10 + 2,
          title: 'Due today',
          body: item.title + ' is due today',
          schedule: { at: due, allowWhileIdle: true }
        });
      }
      if (past > now) {
        out.push({
          id: item.id * 10 + 3,
          title: 'Overdue',
          body: item.title + ' is overdue',
          schedule: { at: past, allowWhileIdle: true }
        });
      } else if (item.status === 'past_due') {
        out.push({
          id: item.id * 10 + 3,
          title: 'Overdue',
          body: item.title + ' is overdue (due ' + item.use_by_date + ')',
          schedule: { at: nineAmTomorrow(), allowWhileIdle: true }
        });
      }
    }
    return out;
  }

  async function sync() {
    try {
      var perm = await LocalNotifications.checkPermissions();
      if (perm.display !== 'granted') {
        perm = await LocalNotifications.requestPermissions();
        if (perm.display !== 'granted') return;
      }

      var res = await fetch(API_URL, { credentials: 'include' });
      if (res.status === 401) return;
      if (!res.ok) return;
      var data = await res.json();
      var items = (data && data.items) || [];

      var pending = await LocalNotifications.getPending();
      if (pending && pending.notifications && pending.notifications.length > 0) {
        await LocalNotifications.cancel({
          notifications: pending.notifications.map(function (n) { return { id: n.id }; })
        });
      }

      var toSchedule = buildNotifications(items, new Date());
      if (toSchedule.length > 0) {
        await LocalNotifications.schedule({ notifications: toSchedule });
      }
    } catch (err) {
      console.warn('Notification sync failed', err);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', sync);
  } else {
    sync();
  }
})();
