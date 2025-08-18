@extends('layout')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Alerts -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Total Utilisateurs</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Utilisateurs Actifs</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['active_users'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Managers</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['managers'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Dernière Connexion</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['last_login'] ?? 'Aucune' }}</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Répartition des Rôles</h3>
                <canvas id="roleChart"></canvas>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Statut des Utilisateurs</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Create User Form -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Créer un utilisateur</h2>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rôle</label>
                        <select name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="member">Membre</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Statut</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="active">Actif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input type="password" name="password" id="create-password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Laisser vide pour générer">
                        <button type="button" onclick="togglePassword('create-password')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5 mt-6">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Créer
                    l'utilisateur</button>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Utilisateurs</h2>
            @if ($users->isEmpty())
                <div class="text-center py-8">
                    <i class="fas fa-users text-gray-400 text-4xl mb-2"></i>
                    <h3 class="text-lg font-semibold text-gray-700">Aucun utilisateur</h3>
                    <p class="text-gray-500">Aucun utilisateur n'a été trouvé.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <!-- Only showing the relevant table section for brevity -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rôle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dernière Connexion</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($users as $u)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $u->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $u->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($u->role) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $u->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($u->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($u->last_login_at)
                                            {{ \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() }}
                                        @else
                                            Jamais
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex gap-2">
                                            <button
                                                onclick="openEditModal({{ $u->id }}, '{{ $u->name }}', '{{ $u->email }}', '{{ $u->role }}', '{{ $u->status }}')"
                                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600"
                                                >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('users.reset_password', $u) }}"
                                                onsubmit="return confirm('Confirmer la réinitialisation du mot de passe ?')">
                                                @csrf @method('PUT')
                                                <input type="password" name="password" class="hidden">
                                                <button type="submit"
                                                    class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600"
                                                    {{ $u->id === Auth::id() ? 'disabled' : '' }}>
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('users.destroy', $u) }}"
                                                onsubmit="return confirm('Confirmer la suppression de cet utilisateur ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                                                    {{ $u->id === Auth::id() ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Edit User Modal -->
        <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Modifier l'utilisateur</h2>
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="name" id="edit-name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="edit-email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Rôle</label>
                        <select name="role" id="edit-role"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="member">Membre</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Statut</label>
                        <select name="status" id="edit-status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="active">Actif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>
                    <div class="mb-4 relative">
                        <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input type="password" name="password" id="edit-password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Laisser vide pour ne pas modifier">
                        <button type="button" onclick="togglePassword('edit-password')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5 mt-6">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeEditModal()"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Annuler</button>
                        <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Chart.js and JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Password toggle
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Modal handling
        function openEditModal(id, name, email, role, status) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-role').value = role;
            document.getElementById('edit-status').value = status;
            document.getElementById('editForm').action = `/users/${id}`;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Charts
        const roleChart = new Chart(document.getElementById('roleChart'), {
            type: 'pie',
            data: {
                labels: ['Membres', 'Managers'],
                datasets: [{
                    data: [{{ $stats['total_users'] - $stats['managers'] }}, {{ $stats['managers'] }}],
                    backgroundColor: ['#3B82F6', '#10B981']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: ['Actifs', 'Suspendus'],
                datasets: [{
                    data: [{{ $stats['active_users'] }},
                        {{ $stats['total_users'] - $stats['active_users'] }}
                    ],
                    backgroundColor: ['#10B981', '#EF4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    </script>
@endsection
