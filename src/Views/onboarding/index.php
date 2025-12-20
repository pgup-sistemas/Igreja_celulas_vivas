<style>
/* === FIX EXCLUSIVO PARA ONBOARDING === */
.onboarding-carousel-inner {
    min-height: 400px;
}

.onboarding-carousel-inner .carousel-item {
    padding-bottom: 1rem;
}

@media (max-width: 768px) {
    .onboarding-carousel-inner {
        min-height: auto;
    }
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">
                    <i class="bi bi-rocket-takeoff me-2"></i>
                    Bem-vindo ao Sistema de Gestão de Células!
                </h4>
            </div>

            <div class="card-body p-0">
                <!-- Progress Bar -->
                <div class="px-4 pt-4">
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-success" id="onboardingProgress" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Carousel -->
                <div id="onboardingCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner onboarding-carousel-inner">

                        <!-- Passo 1 -->
                        <div class="carousel-item active">
                            <div class="p-4 text-center w-100">
                                <i class="bi bi-hand-wave text-primary mb-3" style="font-size: 1.5rem;"></i>
                                <h5 class="mb-4">Olá! Bem-vindo ao Sistema</h5>
                                <p class="text-muted mb-5" style="font-size: 0.9rem;">
                                    Este guia rápido vai te ajudar a conhecer as principais funcionalidades do sistema de gestão de células da igreja.
                                </p>
                                
                                <div class="row g-4 justify-content-center">
                                    <div class="col-md-4">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-people-fill text-success mb-2" style="font-size: 0.8rem;"></i>
                                                <h6 style="font-size: 0.8rem;">Gerencie Células</h6>
                                                <p style="font-size: 0.7rem;">Organize e acompanhe suas células, adicione membros e gerencie informações</p>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="col-md-4">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-calendar-event text-info mb-2" style="font-size: 0.8rem;"></i>
                                                <h6 style="font-size: 0.8rem;">Registre Reuniões</h6>
                                                <p style="font-size: 0.7rem;">Acompanhe reuniões semanais, registrando presença e atividades</p>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="col-md-4">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-bar-chart text-warning mb-2" style="font-size: 0.8rem;"></i>
                                                <h6 style="font-size: 0.8rem;">Relatórios</h6>
                                                <p style="font-size: 0.7rem;">Visualize estatísticas e relatórios detalhados das atividades</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Passo 2 -->
                        <div class="carousel-item">
                            <div class="p-4 text-center">
                                <i class="bi bi-compass text-info mb-3" style="font-size: 1.5rem;"></i>
                                <h5 class="mb-4">Navegação do Sistema</h5>

                                <div class="row g-4 justify-content-center">
                                    <div class="col-md-4">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-house-door text-primary mb-2"></i>
                                                <h6 style="font-size: 0.8rem;">Dashboard</h6>
                                                <p style="font-size: 0.7rem;">Visão geral das suas células e atividades recentes</p>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="col-md-4">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-person-check text-success mb-2"></i>
                                                <h6 style="font-size: 0.8rem;">Perfil</h6>
                                                <p style="font-size: 0.7rem;">Suas informações pessoais e configurações de conta</p>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="col-md-4">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-gear text-secondary mb-2"></i>
                                                <h6 style="font-size: 0.8rem;">Configurações</h6>
                                                <p style="font-size: 0.7rem;">Preferências e configurações do sistema</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Passo 3 -->
                        <div class="carousel-item">
                            <div class="p-4">
                                <h5 class="text-center mb-4">Registrar uma Reunião</h5>
                        
                                <ol style="font-size: 0.85rem;">
                                    <li>Selecione a data e horário da reunião</li>
                                    <li>Escolha a célula e o líder responsável</li>
                                    <li>Informe o número de participantes e visitantes</li>
                                    <li>Registre as atividades realizadas durante a reunião</li>
                                    <li>Salve a reunião para gerar relatórios</li>
                                </ol>
                            </div>
                        </div>
                        
                        <!-- Passo 4 -->
                        <div class="carousel-item">
                            <div class="p-4 text-center">
                                <h5 class="mb-4">Recursos Disponíveis</h5>
                        
                                <div class="alert alert-success">
                                    <strong>Relatórios detalhados:</strong> Acompanhe o crescimento das células e o envolvimento dos membros.<br>
                                    <strong>Dashboard interativo:</strong> Visualize métricas e estatísticas em tempo real.<br>
                                    <strong>Exportações:</strong> Exporte dados para análise e apresentações.
                                </div>
                            </div>
                        </div>

                        <!-- Passo 5 -->
                        <div class="carousel-item">
                            <div class="p-4 text-center">
                                <h5 class="mb-4">Onboarding Concluído 🎉</h5>

                                <button class="btn btn-success btn-sm" id="completeBtn" style="min-width: 120px;">
                                    <span class="btn-text">Concluir Onboarding</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>

                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" id="showAgain" checked>
                                    <label class="form-check-label" style="font-size: 0.8rem;">
                                        Mostrar novamente
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Controls -->
                    <div class="px-4 pb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-outline-secondary" data-bs-slide="prev" data-bs-target="#onboardingCarousel" id="prevBtn">
                                Anterior
                            </button>

                            <span id="stepIndicator" class="small text-muted">Passo 1 de 5</span>

                            <button class="btn btn-primary" data-bs-slide="next" data-bs-target="#onboardingCarousel" id="nextBtn">
                                Próximo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateProgress() {
    const carousel = document.getElementById('onboardingCarousel');
    const items = carousel.querySelectorAll('.carousel-item');
    const active = carousel.querySelector('.carousel-item.active');
    const index = [...items].indexOf(active) + 1;

    document.getElementById('onboardingProgress').style.width = (index / items.length * 100) + '%';
    document.getElementById('stepIndicator').innerText = `Passo ${index} de ${items.length}`;

    document.getElementById('prevBtn').style.visibility = index === 1 ? 'hidden' : 'visible';
}

document.addEventListener('DOMContentLoaded', () => {
    updateProgress();
    document.getElementById('onboardingCarousel')
        .addEventListener('slid.bs.carousel', updateProgress);

    // Adicionar evento para o botão "Concluir Onboarding"
    const completeBtn = document.getElementById('completeBtn');
    if (completeBtn) {
        let isProcessing = false; // Flag to prevent multiple clicks
        
        completeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Prevent multiple clicks
            if (isProcessing) {
                console.log('Already processing, ignoring click');
                return;
            }
            
            isProcessing = true;
            
            // Show loading state briefly
            const btnText = completeBtn.querySelector('.btn-text');
            const spinner = completeBtn.querySelector('.spinner-border');
            
            if (btnText) btnText.textContent = 'Salvando...';
            if (spinner) spinner.classList.remove('d-none');
            
            // Simple timeout then redirect - no complex backend
            setTimeout(() => {
                // Debug current URL
                console.log('Current URL:', window.location.href);
                console.log('Current pathname:', window.location.pathname);
                
                // Determine the correct base path for the application
                const currentPath = window.location.pathname;
                let basePath = '/igreja/public'; // Default for this app structure
                
                // Extract base path from current URL (e.g., /igreja/public/onboarding)
                const pathParts = currentPath.split('/');
                if (pathParts.length >= 3 && pathParts[1] === 'igreja' && pathParts[2] === 'public') {
                    basePath = '/igreja/public';
                }
                
                // Redirect to the admin dashboard for admin users
                window.location.href = basePath + '/admin';
            }, 500);
        });
    }
});
</script>
