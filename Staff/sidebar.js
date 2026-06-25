// sidebar.js — Staff 共用側邊欄（動態載入）
function loadSidebar(activePageId) {
    // Restore saved sidebar width
    var savedWidth = localStorage.getItem('sidebarWidth');
    if (savedWidth) {
        document.documentElement.style.setProperty('--sidebar-width', savedWidth + 'px');
    }

    var sidebarHTML = ''
        + '<div class="sidebar">'
        + '  <div class="logo-area">Premium Living<br><span style="font-size:12px;color:#5f6368;">Admin Console</span></div>'
        + '  <div class="nav-section">'
        + '    <div class="nav-title">Dashboard</div>'
        + '    <a href="/Staff/dashboard.php" class="nav-item' + (activePageId === 'dashboard' ? ' active' : '') + '">🏠 Home</a>'
        + '  </div>'
        + '  <div class="nav-section">'
        + '    <div class="nav-title">📝 Create</div>'
        + '    <a href="/Staff/Create/createFurniture.php" class="nav-item' + (activePageId === 'create-furniture' ? ' active' : '') + '">🪑 New Furniture</a>'
        + '    <a href="/Staff/Create/createMaterial.php" class="nav-item' + (activePageId === 'create-material' ? ' active' : '') + '">📦 New Material</a>'
        + '    <a href="/Staff/Create/createOrder.php" class="nav-item' + (activePageId === 'create-order' ? ' active' : '') + '">📄 New Order</a>'
        + '    <a href="/Staff/Create/createCustomer.php" class="nav-item' + (activePageId === 'create-customer' ? ' active' : '') + '">👥 New Customer</a>'
        + '  </div>'
        + '  <div class="nav-section">'
        + '    <div class="nav-title">👁️ View</div>'
        + '    <a href="/Staff/View/viewFurniture.php" class="nav-item' + (activePageId === 'view-furniture' ? ' active' : '') + '">🪑 Furniture</a>'
        + '    <a href="/Staff/View/viewMaterials.php" class="nav-item' + (activePageId === 'view-materials' ? ' active' : '') + '">📦 Materials</a>'
        + '    <a href="/Staff/View/viewOrders.php" class="nav-item' + (activePageId === 'view-orders' ? ' active' : '') + '">📋 Orders</a>'
        + '    <a href="/Staff/View/viewCustomers.php" class="nav-item' + (activePageId === 'view-customers' ? ' active' : '') + '">👥 Customers</a>'
        + '  </div>'
        + '  <div class="nav-section">'
        + '    <div class="nav-title">⚙️ Manage</div>'
        + '    <a href="/Staff/Manage/manageFurniture.php" class="nav-item' + (activePageId === 'manage-furniture' ? ' active' : '') + '">🪑 Furniture Management</a>'
        + '    <a href="/Staff/Manage/manageMaterials.php" class="nav-item' + (activePageId === 'manage-materials' ? ' active' : '') + '">📦 Materials Management</a>'
        + '    <a href="/Staff/Manage/manageOrders.php" class="nav-item' + (activePageId === 'manage-orders' ? ' active' : '') + '">🔄 Orders Management</a>'
        + '    <a href="/Staff/Manage/manageCustomers.php" class="nav-item' + (activePageId === 'manage-customers' ? ' active' : '') + '">👥 Customers Management</a>'
        + '    <a href="/Staff/Manage/manageStaff.php" class="nav-item' + (activePageId === 'manage-employees' ? ' active' : '') + '">👥 Employees Management</a>'
        + '  </div>'
        + '  <div class="nav-section">'
        + '    <div class="nav-title">📑 Reports</div>'
        + '    <a href="/Staff/reports.php" class="nav-item' + (activePageId === 'reports' ? ' active' : '') + '">📈 Reports Center</a>'
        + '  </div>'
        + '  <div class="nav-section">'
        + '    <div class="nav-title">🔒 Account</div>'
        + '    <a href="/logout.php" class="nav-item">Sign out</a>'
        + '  </div>'
        + '</div>';

    // Inject sidebar into container, handle goes directly into body as flex child
    document.getElementById('sidebar-container').innerHTML = sidebarHTML;

    // Create resize handle as a direct child of body (so body's flex layout can position it)
    var handle = document.createElement('div');
    handle.className = 'sidebar-resize-handle';
    handle.id = 'sidebarResizeHandle';
    var sidebarContainer = document.getElementById('sidebar-container');
    sidebarContainer.parentNode.insertBefore(handle, sidebarContainer.nextSibling);

    // --- Drag-to-resize sidebar ---
    var sidebar = document.querySelector('.sidebar');
    var startX, startWidth;

    function onDragStart(e) {
        e.preventDefault();
        startX = e.clientX;
        startWidth = sidebar.offsetWidth;
        handle.classList.add('active');
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
        document.addEventListener('mousemove', onDragMove);
        document.addEventListener('mouseup', onDragEnd);
    }

    function onDragMove(e) {
        var newWidth = startWidth + (e.clientX - startX);
        if (newWidth < 180) newWidth = 180;
        if (newWidth > 500) newWidth = 500;
        document.documentElement.style.setProperty('--sidebar-width', newWidth + 'px');
    }

    function onDragEnd() {
        handle.classList.remove('active');
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        document.removeEventListener('mousemove', onDragMove);
        document.removeEventListener('mouseup', onDragEnd);
        localStorage.setItem('sidebarWidth', sidebar.offsetWidth);
    }

    handle.addEventListener('mousedown', onDragStart);
}
