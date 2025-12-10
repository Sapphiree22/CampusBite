let salesChartInstance = null;

// --- API HELPER ---
async function api(url, data = null) {
    const options = { method: data ? "POST" : "GET" };
    if (data && !(data instanceof FormData)) {
        options.headers = { "Content-Type": "application/json" };
        options.body = JSON.stringify(data);
    } else if (data) {
        options.body = data;
    }
    try {
        const res = await fetch(url, options);
        return await res.json();
    } catch(e) { console.error(e); return {success:false}; }
}

// --- GLOBAL SWITCHER FUNCTION ---
// Exposed to window so HTML onclick="..." works
window.switchSection = function(target) {
    document.querySelectorAll(".content-section").forEach(sec => sec.classList.add("hidden"));
    document.getElementById(target).classList.remove("hidden");

    // Update Sidebar Highlights
    const pageTitle = document.getElementById("page-title");
    
    // Update button styles manually since we use onclick now
    // Reset all
    document.querySelectorAll(".nav-item").forEach(btn => {
        btn.classList.remove("bg-white", "text-brand", "shadow-md");
        btn.classList.add("hover:bg-white/20", "text-white");
    });

    // Highlight active (Finding the button that calls this section)
    const activeBtn = document.querySelector(`button[onclick="switchSection('${target}')"]`);
    if(activeBtn) {
        activeBtn.classList.remove("hover:bg-white/20", "text-white");
        activeBtn.classList.add("bg-white", "text-brand", "shadow-md");
        if(pageTitle) pageTitle.innerText = activeBtn.innerText.trim();
    }

    // Load Data based on section
    if(target === 'dashboard') loadDashboard();
    if(target === 'reservations') loadReservations();
    if(target === 'menu') loadMenu();
    if(target === 'analytics') loadAnalytics('week');
    if(target === 'users') { loadUsers(); loadStaff(); }
    if(target === 'logs') loadLogs();
}

// --- INIT ---
document.addEventListener("DOMContentLoaded", () => {
    loadDashboard(); // Load default
    
    document.getElementById("analytics-period")?.addEventListener("change", (e) => loadAnalytics(e.target.value));
    document.getElementById("toggle-status-btn")?.addEventListener("click", toggleStatus);
});

// --- DASHBOARD ---
async function loadDashboard() {
    const data = await api("admin_core.php?action=get_dashboard_stats");
    if(data.success) {
        document.getElementById("stat-revenue").innerText = "₱" + parseFloat(data.revenue).toFixed(2);
        document.getElementById("stat-reservations").innerText = data.reservations;
        document.getElementById("stat-users").innerText = data.users;
        
        // Load the mini table on dashboard
        loadDashboardReservations();
    }
}

async function loadDashboardReservations() {
    const data = await api("reservation.php", { action: "view_all_reservations" });
    const tbody = document.getElementById("dashboard-reservations");
    if(data.success && tbody) {
        const pending = data.reservations.filter(r => r.status === 'Pending').slice(0, 5);
        if(pending.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="p-4 text-center text-gray-400">No pending reservations.</td></tr>`;
            return;
        }
        tbody.innerHTML = pending.map(r => `
            <tr>
                <td class="p-4 font-medium">${r.full_name}</td>
                <td class="p-4 text-gray-500">${r.reservation_time}</td>
                <td class="p-4"><span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">Pending</span></td>
                <td class="p-4 text-right">
                    <button onclick="switchSection('reservations')" class="text-blue-500 hover:underline text-xs">Manage</button>
                </td>
            </tr>
        `).join("");
    }
}

// --- RESERVATIONS ---
async function loadReservations() {
    const data = await api("reservation.php", { action: "view_all_reservations" });
    const tbody = document.getElementById("reservations-management-tbody");
    if(data.success && tbody) {
        tbody.innerHTML = data.reservations.map(r => `
            <tr class="border-b hover:bg-gray-50">
                <td class="p-4">#${r.reservation_id}</td>
                <td class="p-4 font-bold text-gray-700">${r.full_name}<br><span class="text-xs font-normal text-gray-400">Table ${r.table_number || '?'}</span></td>
                <td class="p-4">${r.reservation_time}</td>
                <td class="p-4"><span class="px-2 py-1 rounded text-xs font-bold ${r.status==='Confirmed'?'bg-green-100 text-green-700':r.status==='Cancelled'?'bg-red-100 text-red-700':'bg-yellow-100 text-yellow-700'}">${r.status}</span></td>
                <td class="p-4">
                    ${r.status === 'Pending' ? `
                        <button onclick="updateRes(${r.reservation_id}, 'Confirmed', this)" class="text-green-600 hover:text-green-800 mr-2 font-bold text-sm">Accept</button>
                        <button onclick="updateRes(${r.reservation_id}, 'Cancelled', this)" class="text-red-600 hover:text-red-800 font-bold text-sm">Decline</button>
                    ` : '<span class="text-gray-400">-</span>'}
                </td>
            </tr>
        `).join("");
    }
    const status = await api("reservation.php", { action: "get_system_status" });
    const statVal = document.getElementById("system-status-value");
    if(statVal) {
        statVal.innerText = status.system_status;
        statVal.className = status.system_status === "OPEN" ? "font-extrabold text-2xl text-green-600" : "font-extrabold text-2xl text-red-600";
    }
}

async function updateRes(id, status, btn) {
    if(btn) { btn.innerText = "..."; btn.disabled = true; }
    await api("reservation.php", { action: "update_reservation", reservation_id: id, status });
    loadReservations();
    loadDashboard();
}

async function toggleStatus() {
    const current = document.getElementById("system-status-value").innerText;
    const newStatus = current === "OPEN" ? "CLOSED" : "OPEN";
    await api("reservation.php", { action: "set_system_status", status: newStatus });
    loadReservations();
}

// --- MENU ---
async function loadMenu() {
    const items = await api("menu_actions.php");
    const tbody = document.querySelector("#menu-items-table tbody");
    if(!tbody) return;
    tbody.innerHTML = items.map(i => `
        <tr class="border-b hover:bg-gray-50">
            <td class="p-4"><img src="${i.image||'images/default.jpg'}" class="w-12 h-12 rounded object-cover border"></td>
            <td class="p-4 font-bold text-gray-700">${i.name}</td>
            <td class="p-4 text-green-600 font-mono font-bold">₱${parseFloat(i.variants[0].price).toFixed(2)}</td>
            <td class="p-4"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-bold">${i.variants[0].stock}</span></td>
            <td class="p-4"><button onclick="deleteItem(${i.item_id})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button></td>
        </tr>
    `).join("");
}
document.getElementById("add-menu-item-form")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append("action", "add_item");
    const res = await api("menu_actions.php", formData);
    if(res.success) { alert("Added!"); e.target.reset(); loadMenu(); }
});
async function deleteItem(id) {
    if(confirm("Delete?")) { await api(`menu_actions.php?action=delete_item&item_id=${id}`); loadMenu(); }
}

// --- ANALYTICS ---
async function loadAnalytics(period) {
    const res = await api(`admin_core.php?action=get_sales_chart&period=${period}`);
    if(!res.success) return;
    const ctx = document.getElementById('salesChart').getContext('2d');
    if (salesChartInstance) salesChartInstance.destroy();
    salesChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: res.labels,
            datasets: [{
                label: `Revenue (${period})`,
                data: res.data,
                backgroundColor: 'rgba(249, 115, 22, 0.6)', // Brand Orange
                borderColor: 'rgba(249, 115, 22, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}

// --- USERS & LOGS ---
async function loadUsers() {
    const data = await api("admin_core.php?action=get_users");
    document.getElementById("users-table-body").innerHTML = data.users.map(u => `<tr><td class="p-3 font-medium">${u.full_name}</td><td class="p-3 text-gray-500">${u.email}</td><td class="p-3 text-gray-500">${u.course}</td></tr>`).join("");
}
async function loadStaff() {
    const data = await api("admin_core.php?action=get_staff");
    document.getElementById("staff-table-body").innerHTML = data.staff.map(u => `<tr><td class="p-3 font-medium">${u.User_name}</td><td class="p-3"><span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-bold">${u.role}</span></td></tr>`).join("");
}
async function loadLogs() {
    const data = await api("admin_core.php?action=get_logs");
    document.getElementById("logs-table-body").innerHTML = data.logs.map(l => `<tr class="border-b hover:bg-gray-50"><td class="p-4 text-gray-400 text-xs whitespace-nowrap">${l.log_date}</td><td class="p-4 font-bold text-gray-700">${l.action}</td><td class="p-4 text-gray-600 text-sm">${l.description}</td></tr>`).join("");
}