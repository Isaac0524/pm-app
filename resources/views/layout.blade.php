<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PM App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="mobile-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h2>Gestion de Projets</h2>
        </div>
        <div class="header-right">
            @auth
                <div class="profile-dropdown-container">
                    <button class="profile-trigger" onclick="toggleProfileDropdown()">
                        <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </button>

                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="profile-header">
                            <div class="profile-avatar-large">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="profile-info">
                                <div class="profile-name">{{ auth()->user()->name }}</div>
                                <div class="profile-email">{{ auth()->user()->email }}</div>
                            </div>
                        </div>

                        <div class="profile-details">
                            <div class="profile-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Compte créé le</span>
                                <span class="profile-value">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="profile-item">
                                <i class="fas fa-user-tag"></i>
                                <span>Rôle</span>
                                <span class="profile-value">{{ ucfirst(auth()->user()->role) }}</span>
                            </div>
                        </div>

                        <div class="profile-actions">
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="logout-dropdown-btn">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="login-btn">Connexion</a>
            @endauth
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <ul class="nav-menu">
                <li><a href="{{ route('dashboard') }}" class="nav-link"><i class="fas fa-chart-bar"></i> Tableau de
                        bord</a></li>
                <li><a href="{{ route('projects.index') }}" class="nav-link"><i class="fas fa-folder"></i> Projets</a>
                </li>
                @auth
                    @if (auth()->user()->isMember())
                        <li><a href="{{ route('my.work') }}" class="nav-link"><i class="fas fa-briefcase"></i> Mon
                                travail</a></li>
                        <li><a href="{{ route('daily_reports.my_day') }}" class="nav-link"><i
                                    class="fas fa-calendar-day"></i> Ma journée</a></li>
                    @endif
                    @if (auth()->user()->isManager())
                        <li><a href="{{ route('daily_reports.daily_reports') }}" class="nav-link"><i
                                    class="fas fa-file-alt"></i> Rapports Journaliers</a></li>
                        <li><a href="{{ route('teams.index') }}" class="nav-link"><i class="fas fa-users"></i> Équipes</a>
                        </li>
                        <li><a href="{{ route('users.index') }}" class="nav-link"><i class="fas fa-user-friends"></i>
                                Utilisateurs</a></li>
                    @endif
                @endauth
            </ul>
        </div>
    </nav>

    <!-- Overlay pour mobile -->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-wrapper">
            <!-- Messages Flash -->
            @if (session('ok'))
                <div class="flash-message success">
                    {{ session('ok') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="flash-message error">
                    @foreach ($errors->all() as $e)
                        <p>{{ $e }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Contenu de la page -->
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; {{ date('Y') }} @ISC - COMPANY Tous droits réservés</p>
        </footer>
    </main>

    <!-- Floating AI Button -->
    @if (auth()->user()->isManager())
        <button class="ai-button" onclick="toggleAISidebar()">
            <i class="fas fa-robot"></i>
        </button>
    @endif

    <!-- AI Sidebar -->
    <div class="ai-sidebar" id="aiSidebar">
        <div class="ai-sidebar-header">
            <h3>Assistant IA</h3>
            <button class="ai-close-btn" onclick="toggleAISidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="ai-sidebar-content">
            <div class="ai-chat-container">
                <div class="ai-message ai-message-bot">
                    <div class="ai-message-content">
                        Bienvenue dans l'Assistant IA ! Comment puis-je vous aider aujourd'hui ?
                    </div>
                </div>
                <!-- Placeholder for user message -->
                <div class="ai-message ai-message-user" style="display: none;">
                    <div class="ai-message-content">
                        Exemple de message utilisateur
                    </div>
                </div>
            </div>
            <div class="ai-input-container">
                <textarea class="ai-input" placeholder="Posez votre question..."></textarea>
                <button class="ai-submit-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- AI Sidebar Overlay -->
    <div class="ai-sidebar-overlay" id="aiSidebarOverlay" onclick="toggleAISidebar()"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Global variables for AI chat
        let chatSession = [];
        let isProcessing = false;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Profile dropdown functionality
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // AI Sidebar functionality
        function toggleAISidebar() {
            const aiSidebar = document.getElementById('aiSidebar');
            const aiOverlay = document.getElementById('aiSidebarOverlay');

            aiSidebar.classList.toggle('active');
            aiOverlay.classList.toggle('active');

            // Focus on input when opening
            if (aiSidebar.classList.contains('active')) {
                setTimeout(() => {
                    document.querySelector('.ai-input').focus();
                }, 300);
            }
        }

        // AI Chat functionality
        async function sendAIMessage() {
            const input = document.querySelector('.ai-input');
            const message = input.value.trim();

            if (!message || isProcessing) return;

            // Add user message to chat
            addChatMessage(message, 'user');
            input.value = '';
            isProcessing = true;

            // Show loading indicator
            addChatMessage('...', 'bot', true);

            try {
                const response = await fetch('/api/ai/chat/handle-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: message,
                        context: chatSession
                    })
                });

                const data = await response.json();

                // Remove loading indicator
                removeLoadingMessage();

                if (data.reply) {
                    addChatMessage(data.reply, 'bot');
                    chatSession.push({
                        user: message,
                        bot: data.reply
                    });

                    // Si des données sont retournées (projet, tâche, etc.)
                    if (data.project || data.task) {
                        // Afficher les informations supplémentaires
                        const info = data.project ? `Projet: ${data.project.title}` :
                                   data.task ? `Tâche: ${data.task.title}` : '';
                        if (info) {
                            addChatMessage(`✅ ${info}`, 'bot');
                        }
                    }
                } else {
                    addChatMessage('Désolé, une erreur est survenue. Veuillez réessayer.', 'bot');
                }
            } catch (error) {
                removeLoadingMessage();
                addChatMessage('Erreur de connexion au service IA. Veuillez réessayer.', 'bot');
                console.error('AI Chat Error:', error);
            } finally {
                isProcessing = false;
            }
        }

        function addChatMessage(text, type, isLoading = false) {
            const container = document.querySelector('.ai-chat-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `ai-message ai-message-${type}`;

            if (isLoading) {
                messageDiv.innerHTML = `
                    <div class="ai-message-content">
                        <div class="loading-dots">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                `;
                messageDiv.id = 'loading-message';
            } else {
                messageDiv.innerHTML = `<div class="ai-message-content">${text}</div>`;
            }

            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        function removeLoadingMessage() {
            const loadingMsg = document.getElementById('loading-message');
            if (loadingMsg) loadingMsg.remove();
        }

        // Handle Enter key in chat input
        document.addEventListener('DOMContentLoaded', function() {
            const aiInput = document.querySelector('.ai-input');
            const aiSubmitBtn = document.querySelector('.ai-submit-btn');

            if (aiInput) {
                aiInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendAIMessage();
                    }
                });
            }

            if (aiSubmitBtn) {
                aiSubmitBtn.addEventListener('click', sendAIMessage);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const trigger = document.querySelector('.profile-trigger');

            if (!trigger.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Close dropdown and AI sidebar on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const dropdown = document.getElementById('profileDropdown');
                dropdown.classList.remove('show');
                const aiSidebar = document.getElementById('aiSidebar');
                const aiOverlay = document.getElementById('aiSidebarOverlay');
                aiSidebar.classList.remove('active');
                aiOverlay.classList.remove('active');
            }
        });

        // Close sidebars when resizing to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                const aiSidebar = document.getElementById('aiSidebar');
                const aiOverlay = document.getElementById('aiSidebarOverlay');
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                aiSidebar.classList.remove('active');
                aiOverlay.classList.remove('active');
            }
        });
    </script>

    <style>
        /* Reset et variables */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #0895f3;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --sidebar-width: 250px;
            --header-height: 64px;
            --border-radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --transition: all 0.3s ease;
            --ai-sidebar-width: 350px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--gray-600);
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: var(--transition);
        }

        .mobile-menu-btn:hover {
            background: var(--gray-100);
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 500;
            font-size: 14px;
            color: var(--gray-900);
        }

        .user-role {
            font-size: 12px;
            color: var(--gray-500);
        }

        .logout-btn,
        .login-btn {
            background-color: #8B0000;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: var(--transition);
            color: white;
            font-weight: 500;
        }

        .logout-btn:hover,
        .login-btn:hover {
            background-color: #a31616;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        /* Profile Dropdown Styles */
        .profile-dropdown-container {
            position: relative;
        }

        .profile-trigger {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: var(--transition);
        }

        .profile-trigger:hover {
            transform: scale(1.05);
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 320px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--gray-200);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            border-bottom: 1px solid var(--gray-200);
        }

        .profile-avatar-large {
            width: 48px;
            height: 48px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-weight: 600;
            font-size: 16px;
            color: var(--gray-900);
            margin-bottom: 2px;
        }

        .profile-email {
            font-size: 14px;
            color: var(--gray-600);
        }

        .profile-details {
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-200);
        }

        .profile-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            font-size: 14px;
            color: var(--gray-700);
        }

        .profile-item i {
            width: 16px;
            color: var(--gray-500);
        }

        .profile-value {
            margin-left: auto;
            font-weight: 500;
            color: var(--gray-900);
        }

        .profile-actions {
            padding: 12px 20px;
        }

        .profile-link,
        .logout-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 10px 12px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .profile-link:hover,
        .logout-dropdown-btn:hover {
            background: var(--gray-100);
            color: var(--gray-900);
        }

        .logout-dropdown-btn {
            color: #dc2626;
            margin-top: 4px;
        }

        .logout-dropdown-btn:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Mobile adjustments */
        @media (max-width: 768px) {
            .profile-dropdown {
                width: 280px;
                right: -10px;
            }
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            background: #0a1a2f;
            border-right: none;
            overflow-y: auto;
            transition: var(--transition);
            z-index: 900;
        }

        .sidebar-content {
            padding: 24px 0;
        }

        .sidebar-header {
            padding: 0 24px 24px;
            border-bottom: 1px solid var(--gray-200);
        }

        .sidebar-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
        }

        .nav-menu {
            list-style: none;
            padding: 24px 0 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: #cfd8e3;
            text-decoration: none;
            transition: var(--transition);
            font-size: 15px;
        }

        .nav-link:hover {
            background: #123456;
            color: #ffffff;
        }

        .nav-link.active {
            background: #1565c0;
            color: white;
            border-right: 3px solid #1e88e5;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 850;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* AI Button */
        .ai-button {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6B46C1 0%, #00C4B4 100%);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
            z-index: 1000;
        }

        .ai-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

        /* AI Sidebar */
        .ai-sidebar {
            position: fixed;
            top: var(--header-height);
            right: 0;
            width: var(--ai-sidebar-width);
            height: calc(100vh - var(--header-height));
            background: white;
            box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1);
            transform: translateX(100%);
            transition: var(--transition);
            z-index: 900;
            display: flex;
            flex-direction: column;
        }

        .ai-sidebar.active {
            transform: translateX(0);
        }

        .ai-sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: linear-gradient(90deg, #6B46C1 0%, #00C4B4 100%);
            color: white;
            border-bottom: 1px solid var(--gray-200);
        }

        .ai-sidebar-header h3 {
            font-size: 18px;
            font-weight: 600;
        }

        .ai-close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: var(--transition);
        }

        .ai-close-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .ai-sidebar-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 24px;
            overflow: hidden;
        }

        .ai-chat-container {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding-bottom: 16px;
        }

        .ai-message {
            display: flex;
            flex-direction: column;
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.5;
        }

        .ai-message-bot {
            background: var(--gray-100);
            color: var(--gray-900);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .ai-message-user {
            background: var(--primary-color);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .ai-message-content {
            word-wrap: break-word;
        }

        .ai-input-container {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            border-top: 1px solid var(--gray-200);
            padding-top: 16px;
        }

        .ai-input {
            flex: 1;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            resize: none;
            height: 48px;
            transition: var(--transition);
            font-family: inherit;
        }

        .ai-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .ai-submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
        }

        .ai-submit-btn:hover {
            background: #1e88e5;
        }

        .ai-sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 850;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .ai-sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
            padding: 24px;
        }

        .flash-message {
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            font-size: 14px;
        }

        .flash-message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .flash-message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .footer {
            background: white;
            border-top: 1px solid var(--gray-200);
            padding: 16px;
            text-align: center;
            color: var(--gray-500);
            font-size: 14px;
        }

        @media (max-width: 1024px) {
            .mobile-menu-btn {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .user-details {
                display: none;
            }

            .ai-sidebar {
                width: var(--ai-sidebar-width);
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 16px;
            }

            .content-wrapper {
                padding: 16px;
            }

            .logo {
                font-size: 20px;
            }

            .ai-button {
                width: 50px;
                height: 50px;
                font-size: 20px;
                bottom: 16px;
                right: 16px;
            }

            .ai-sidebar {
                width: 100%;
                max-width: var(--ai-sidebar-width);
            }
        }
    </style>
</body>

</html>
