class ReservationCalendar {
    constructor() {
        this.currentDate = new Date();
        this.unavailableDates = new Set();
        this.serviceReservations = new Map(); // Map of date -> services booked
        this.availableServices = []; // All available services
        this.init();
    }

    init() {
        this.renderCalendar();
        this.bindEvents();
        this.loadCalendarData();
    }

    async loadCalendarData() {
        try {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth() + 1;
            
            const response = await fetch(`reservation-backend.php?action=get_calendar_data&year=${year}&month=${String(month).padStart(2, '0')}`);
            const data = await response.json();
            
            if (data.success) {
                this.processCalendarData(data.data);
                this.renderCalendar();
            }
        } catch (error) {
            console.error('Error loading calendar data:', error);
        }
    }

    processCalendarData(data) {
        this.unavailableDates.clear();
        this.serviceReservations.clear();
        this.availableServices = data.services || [];

        if (data.reservations && Array.isArray(data.reservations)) {
            data.reservations.forEach(reservation => {
                try {
                    const startDate = new Date(reservation.reservation_date_start);
                    let endDate = new Date(reservation.reservation_date_end || reservation.reservation_date_start);
                    
                    if (endDate < startDate) {
                        endDate = new Date(startDate);
                    }
                    
                    const currentDate = new Date(startDate);
                    while (currentDate <= endDate) {
                        const dateKey = this.formatDateKey(currentDate);
                        
                        // Mark date as unavailable
                        this.unavailableDates.add(dateKey);
                        
                        // Track which services are booked on this date
                        if (!this.serviceReservations.has(dateKey)) {
                            this.serviceReservations.set(dateKey, []);
                        }
                        
                        const serviceInfo = {
                            id: reservation.service_id,
                            name: reservation.service_name,
                            quantity: reservation.quantity || 1
                        };
                        
                        // Check if this service is already in the list for this date
                        const existingService = this.serviceReservations.get(dateKey).find(s => s.id === serviceInfo.id);
                        if (existingService) {
                            existingService.quantity += serviceInfo.quantity;
                        } else {
                            this.serviceReservations.get(dateKey).push(serviceInfo);
                        }
                        
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                } catch (error) {
                    console.error('Error processing reservation:', error);
                }
            });
        }
    }

    formatDateKey(date) {
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }

    renderCalendar() {
        const calendarEl = document.getElementById('availability-calendar');
        if (!calendarEl) return;

        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        let calendarHTML = `
            <div class="calendar-header">
                <button class="calendar-nav-btn" id="prev-month">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h4 class="mb-0">${this.currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</h4>
                <button class="calendar-nav-btn" id="next-month">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="calendar-grid">
        `;

        // Day headers
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayNames.forEach(day => {
            calendarHTML += `<div class="calendar-day-header">${day}</div>`;
        });

        // Empty cells for days before the first day of the month
        for (let i = 0; i < startingDay; i++) {
            calendarHTML += `<div class="calendar-day other-month"></div>`;
        }

        // Days of the month
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayKey = this.formatDateKey(today);

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateKey = this.formatDateKey(date);
            const isUnavailable = this.unavailableDates.has(dateKey);
            const isToday = dateKey === todayKey;
            const bookedServices = this.serviceReservations.get(dateKey) || [];
            const availableServices = this.getAvailableServicesForDate(dateKey);
            
            let dayClass = 'calendar-day';
            let statusText = '';
            
            if (isToday) dayClass += ' today';
            
            if (isUnavailable) {
                if (availableServices.length === 0) {
                    dayClass += ' fully-booked';
                    statusText = '<span class="status-dot fully-booked-dot" title="Fully Booked">●</span>';
                } else {
                    dayClass += ' partially-booked';
                    statusText = '<span class="status-dot partially-booked-dot" title="Partially Booked">●</span>';
                }
            } else {
                dayClass += ' available';
                statusText = '<span class="status-dot available-dot" title="All Services Available">●</span>';
            }

            calendarHTML += `
                <div class="${dayClass}" data-date="${dateKey}">
                    <div class="day-number">${day}</div>
                    ${statusText}
                </div>
            `;
        }

        // Fill remaining cells to complete the grid (6 rows)
        const totalCells = startingDay + daysInMonth;
        const remainingCells = 42 - totalCells; // 6 rows × 7 days = 42 cells
        for (let i = 0; i < remainingCells; i++) {
            calendarHTML += `<div class="calendar-day other-month"></div>`;
        }

        calendarHTML += '</div>';
        calendarEl.innerHTML = calendarHTML;

        this.bindDayClickEvents();
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('#prev-month')) {
                this.navigateMonth(-1);
            } else if (e.target.closest('#next-month')) {
                this.navigateMonth(1);
            }
        });
    }

    navigateMonth(direction) {
        this.currentDate.setMonth(this.currentDate.getMonth() + direction);
        this.loadCalendarData();
    }

    bindDayClickEvents() {
        document.querySelectorAll('.calendar-day[data-date]').forEach(day => {
            day.addEventListener('click', () => {
                const date = day.getAttribute('data-date');
                this.showDateDetails(date);
            });
        });
    }

    showDateDetails(date) {
        const modalTitle = document.getElementById('dateDetailsTitle');
        const modalContent = document.getElementById('dateDetailsContent');
        
        const dateObj = new Date(date + 'T00:00:00');
        const formattedDate = dateObj.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });

        modalTitle.textContent = `Service Availability for ${formattedDate}`;

        const isUnavailable = this.unavailableDates.has(date);
        const bookedServices = this.serviceReservations.get(date) || [];
        const availableServices = this.getAvailableServicesForDate(date);
        
        let content = '';

        if (isUnavailable) {
            if (availableServices.length === 0) {
                // Fully booked
                content = `
                    <div class="availability-status text-center text-danger mb-3">
                        <i class="fas fa-times-circle fa-3x mb-2"></i>
                        <h5 class="text-danger">Fully Booked</h5>
                        <p class="mb-3">All services are booked on this date.</p>
                    </div>
                `;
            } else {
                // Partially booked
                content = `
                    <div class="availability-status text-center text-warning mb-3">
                        <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                        <h5 class="text-warning">Partially Booked</h5>
                        <p class="mb-3">Some services are unavailable on this date.</p>
                    </div>
                `;
            }
            
            // Show booked services
            if (bookedServices.length > 0) {
                content += `
                    <div class="booked-services mb-3">
                        <h6 class="border-bottom pb-2">Booked Services:</h6>
                        <div class="service-list">
                `;
                
                bookedServices.forEach(service => {
                    content += `
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>${service.name}</strong>
                                ${service.quantity > 1 ? `<small class="text-muted ms-2">(x${service.quantity})</small>` : ''}
                            </div>
                            <span class="badge bg-danger">Unavailable</span>
                        </div>
                    `;
                });
                
                content += `</div></div>`;
            }
            
            // Show available services
            if (availableServices.length > 0) {
                content += `
                    <div class="available-services">
                        <h6 class="border-bottom pb-2 text-success">Available Services:</h6>
                        <div class="service-list">
                `;
                
                availableServices.forEach(service => {
                    content += `
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>${service.service_name}</strong>
                                ${service.description ? `<br><small class="text-muted">${service.description}</small>` : ''}
                            </div>
                            <span class="badge bg-success">Available</span>
                        </div>
                    `;
                });
                
                content += `</div></div>`;
            }
        } else {
            // All services are available
            content = `
                <div class="availability-status text-center text-success mb-3">
                    <i class="fas fa-check-circle fa-3x mb-2"></i>
                    <h5 class="text-success">All Services Available</h5>
                    <p class="mb-3">All services are available for reservation on this date.</p>
                </div>
                <div class="available-services">
                    <h6 class="border-bottom pb-2">Available Services:</h6>
                    <div class="service-list">
            `;
            
            this.availableServices.forEach(service => {
                content += `
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <strong>${service.service_name}</strong>
                            ${service.description ? `<br><small class="text-muted">${service.description}</small>` : ''}
                        </div>
                        <span class="badge bg-success">Available</span>
                    </div>
                `;
            });
            
            content += `</div></div>`;
        }

        modalContent.innerHTML = content;

        const modal = new bootstrap.Modal(document.getElementById('dateDetailsModal'));
        modal.show();
    }

    getAvailableServicesForDate(date) {
        const bookedServices = this.serviceReservations.get(date) || [];
        const bookedServiceIds = new Set(bookedServices.map(s => s.id));
        
        return this.availableServices.filter(service => !bookedServiceIds.has(service.id));
    }

    // Utility method to check specific service availability
    isServiceAvailable(serviceId, date) {
        const bookedServices = this.serviceReservations.get(date) || [];
        return !bookedServices.some(service => service.id === serviceId);
    }

    // Method to get service availability summary for a date
    getServiceAvailabilitySummary(date) {
        const bookedServices = this.serviceReservations.get(date) || [];
        const availableServices = this.getAvailableServicesForDate(date);
        
        return {
            date: date,
            isFullyBooked: availableServices.length === 0,
            isPartiallyBooked: bookedServices.length > 0 && availableServices.length > 0,
            isFullyAvailable: bookedServices.length === 0,
            bookedServices: bookedServices,
            availableServices: availableServices,
            totalBooked: bookedServices.length,
            totalAvailable: availableServices.length,
            totalServices: this.availableServices.length
        };
    }
}

// Enhanced initialization with error handling
document.addEventListener('DOMContentLoaded', function() {
    try {
        const calendar = new ReservationCalendar();
        
        // Make calendar globally available for debugging
        window.reservationCalendar = calendar;
        
        // Add global method to check service availability
        window.checkServiceAvailability = function(serviceId, date) {
            return calendar.isServiceAvailable(serviceId, date);
        };
        
        // Add global method to get availability summary
        window.getAvailabilitySummary = function(date) {
            return calendar.getServiceAvailabilitySummary(date);
        };
        
        console.log('Reservation Calendar initialized successfully');
    } catch (error) {
        console.error('Failed to initialize Reservation Calendar:', error);
        
        // Fallback: Show error message in calendar container
        const calendarEl = document.getElementById('availability-calendar');
        if (calendarEl) {
            calendarEl.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load calendar. Please refresh the page.
                </div>
            `;
        }
    }
});

// Additional utility functions for service availability
class ServiceAvailabilityUtils {
    static formatServiceList(services) {
        if (!services || services.length === 0) {
            return '<span class="text-muted">No services</span>';
        }
        
        return services.map(service => 
            `<span class="badge bg-secondary me-1">${service.name}</span>`
        ).join('');
    }
    
    static createAvailabilityBadge(isAvailable) {
        return isAvailable ? 
            '<span class="badge bg-success">Available</span>' : 
            '<span class="badge bg-danger">Booked</span>';
    }
    
    static compareDates(date1, date2) {
        const d1 = new Date(date1);
        const d2 = new Date(date2);
        d1.setHours(0, 0, 0, 0);
        d2.setHours(0, 0, 0, 0);
        return d1.getTime() === d2.getTime();
    }
}

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ReservationCalendar, ServiceAvailabilityUtils };
}