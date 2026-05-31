// Global variables
let selectedServices = {};
let allServices = [];

// Fetch services on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch('get_services_json.php')
        .then(res => res.json())
        .then(data => {
            allServices = data;
            renderServiceList();
        });
    
    // Sidebar navigation
    const navBtns = document.querySelectorAll('.nav-btn');
    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const section = btn.getAttribute('data-section');
            if (section) showSidebarContent(section);
        });
    });
    
    // Quote button
    const quoteBtn = document.getElementById('request-quote');
    if (quoteBtn) {
        quoteBtn.addEventListener('click', requestQuotation);
    }
    
    // Track order button
    const trackBtn = document.getElementById('track-order');
    if (trackBtn) {
        trackBtn.addEventListener('click', () => {
            document.getElementById('gantt-view').style.display = 'block';
        });
    }
    
    // Opinion form
    const opinionForm = document.getElementById('opinionSubmit');
    if (opinionForm) {
        opinionForm.addEventListener('submit', submitOpinion);
    }
    
    // Click outside sidebar to close
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar && !sidebar.contains(event.target) && !event.target.classList.contains('nav-btn')) {
            sidebar.classList.remove('open');
        }
    });
});

function renderServiceList() {
    const container = document.getElementById('services-list-container');
    if (!container) return;
    
    const categories = [...new Map(allServices.map(s => [s.category, s.category])).values()];
    let html = '<h3>Select Required Services</h3>';
    
    categories.forEach(cat => {
        html += `<div class="service-category"><h4>${escapeHtml(cat)}</h4>`;
        allServices.filter(s => s.category === cat).forEach(service => {
            html += `<label style="display:block; margin:5px 0;">
                        <input type="checkbox" value="${service.id}" data-name="${escapeHtml(service.service_name)}" data-charge="${service.charge}">
                        ${escapeHtml(service.service_name)} - $${service.charge}
                     </label>`;
        });
        html += `</div>`;
    });
    
    container.innerHTML = html;
    
    // Attach change events
    document.querySelectorAll('#services-list-container input[type=checkbox]').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = this.value;
            const name = this.dataset.name;
            const charge = parseFloat(this.dataset.charge);
            
            if (this.checked) {
                selectedServices[id] = { name, charge };
            } else {
                delete selectedServices[id];
            }
            updateQuotation();
        });
    });
}

function updateQuotation() {
    let subtotal = 0;
    let listHtml = '';
    
    for (let id in selectedServices) {
        subtotal += selectedServices[id].charge;
        listHtml += `<li>${escapeHtml(selectedServices[id].name)} - $${selectedServices[id].charge}</li>`;
    }
    
    const tax = subtotal * 0.10;
    const total = subtotal + tax;
    
    document.getElementById('selected-services-list').innerHTML = `<ul>${listHtml}</ul>`;
    document.getElementById('subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('tax').innerText = tax.toFixed(2);
    document.getElementById('total').innerText = total.toFixed(2);
}

function requestQuotation() {
    if (Object.keys(selectedServices).length === 0) {
        alert('Please select at least one service');
        return;
    }
    
    const quotationData = {
        services: selectedServices,
        subtotal: document.getElementById('subtotal').innerText,
        tax: document.getElementById('tax').innerText,
        total: document.getElementById('total').innerText
    };
    
    fetch('save_quotation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(quotationData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            alert('Quotation requested successfully!\n\nPayment Policy:\n• 50% advance before start\n• 35% on submission of completion report\n• 15% final after review period\n\nOur team will contact you within 24 hours.');
        } else {
            alert('Error saving quotation. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    });
}

function submitOpinion(e) {
    e.preventDefault();
    
    // Get the CSRF token from the form
    const csrfToken = this.querySelector('input[name="csrf_token"]').value;
    const formData = new FormData(this);
    
    fetch('save_opinion.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            this.reset();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    });
}

function showSidebarContent(section) {
    const sidebar = document.getElementById('sidebar');
    const sidebarContent = document.getElementById('sidebar-content');
    
    // Hide all main content sections
    document.getElementById('quotation-area').style.display = 'none';
    document.getElementById('gantt-view').style.display = 'none';
    document.getElementById('achievements-list').style.display = 'none';
    document.getElementById('contact-info').style.display = 'none';
    document.getElementById('icorner-links').style.display = 'none';
    document.getElementById('opinion-form').style.display = 'none';
    
    if (section === 'we') {
        sidebarContent.innerHTML = `<h3>We</h3>
            <ul>
                <li>Our Journey</li>
                <li>Innovative Teams
                    <ul class="submenu">
                        <li>Business Consultancies Team</li>
                        <li>Website Development Team</li>
                        <li>Mobile Apps Development Team</li>
                        <li>SaaS Program Team</li>
                        <li>SEO Team</li>
                    </ul>
                </li>
                <li>Our Destination: Your Satisfaction</li>
            </ul>`;
        sidebar.classList.add('open');
    } 
    else if (section === 'foryou') {
        document.getElementById('quotation-area').style.display = 'block';
        sidebarContent.innerHTML = `<h3>For You - Services</h3><p>Select services from the checklist below:</p>`;
        sidebar.classList.add('open');
    }
    else if (section === 'achieved') {
        document.getElementById('achievements-list').style.display = 'block';
        sidebar.classList.remove('open');
    }
    else if (section === 'communicate') {
        document.getElementById('contact-info').style.display = 'block';
        document.getElementById('opinion-form').style.display = 'block';
        sidebar.classList.remove('open');
    }
    else if (section === 'icorner') {
        document.getElementById('icorner-links').style.display = 'block';
        sidebar.classList.remove('open');
    }
}

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}