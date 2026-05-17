<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver — {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --amber:     #f59e0b;
            --amber-l:   #fcd34d;
            --amber-dim: rgba(245,158,11,.13);
            --amber-bd:  rgba(245,158,11,.3);
            --green:     #22c55e;
            --red:       #ef4444;
            --s0:        #0b0f1a;
            --s1:        #0d1120;
            --s2:        #111827;
            --s3:        #1f2937;
            --s4:        #374151;
            --bd:        rgba(255,255,255,.07);
            --bd2:       rgba(255,255,255,.12);
            --text:      #f9fafb;
            --mut:       #6b7280;
            --mut2:      #9ca3af;
        }

        html, body {
            min-height: 100%;
            background: var(--s0);
            color: var(--text);
            font-family: 'DM Sans', ui-sans-serif, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Subtle grid texture */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(245,158,11,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(245,158,11,.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        /* Ambient glow */
        .glow-top {
            position: fixed;
            top: -160px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 320px;
            background: radial-gradient(ellipse at center, rgba(245,158,11,.12) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* Layout */
        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 48px 16px 64px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 36px;
            animation: fadeDown .5s ease both;
        }
        .logo-ring {
            width: 72px; height: 72px;
            border-radius: 20px;
            border: 1px solid var(--amber-bd);
            background: var(--amber-dim);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            overflow: hidden;
        }
        .logo-ring img { width: 100%; height: 100%; object-fit: cover; border-radius: 18px; }
        .logo-ring svg { width: 32px; height: 32px; color: var(--amber); }
        .header h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -.02em;
            color: var(--text);
            margin-bottom: 6px;
        }
        .header p {
            font-size: 13px;
            color: var(--mut2);
        }

        /* Card */
        .card {
            width: 100%;
            max-width: 480px;
            background: var(--s2);
            border: 1px solid var(--bd);
            border-radius: 24px;
            overflow: hidden;
            animation: fadeUp .45s ease both .1s;
        }

        /* Section */
        .section {
            padding: 24px 24px 0;
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .section-icon {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: var(--amber-dim);
            border: 1px solid var(--amber-bd);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .section-icon svg { width: 16px; height: 16px; color: var(--amber); }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        /* Divider */
        .divider {
            height: 1px;
            background: var(--bd);
            margin: 20px 24px;
        }

        /* Field */
        .field { margin-bottom: 16px; }
        .field:last-child { margin-bottom: 0; }
        .field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--mut2);
            margin-bottom: 7px;
        }
        .field label .opt {
            text-transform: none;
            font-weight: 400;
            letter-spacing: 0;
            color: var(--mut);
        }

        /* Input */
        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            background: var(--s3);
            border: 1px solid var(--bd2);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
            -webkit-appearance: none;
            appearance: none;
        }
        input::placeholder, textarea::placeholder { color: var(--mut); }
        input:focus, select:focus, textarea:focus {
            border-color: var(--amber-bd);
            box-shadow: 0 0 0 3px rgba(245,158,11,.1);
            background: var(--s4);
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) opacity(.4);
            cursor: pointer;
        }

        /* Grid 2 cols */
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* Party size pills */
        .party-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .party-btn {
            width: 42px; height: 42px;
            border-radius: 12px;
            border: 1px solid var(--bd2);
            background: var(--s3);
            color: var(--mut2);
            font-size: 13px;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s;
        }
        .party-btn:hover { border-color: var(--amber-bd); color: var(--amber); }
        .party-btn.active {
            background: var(--amber-dim);
            border-color: var(--amber-bd);
            color: var(--amber);
            box-shadow: 0 0 12px rgba(245,158,11,.15);
        }

        /* Textarea */
        textarea { resize: none; line-height: 1.5; }

        /* Error box */
        .error-box {
            margin: 16px 24px 0;
            padding: 12px 14px;
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.25);
            border-radius: 12px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .error-box svg { width: 15px; height: 15px; color: var(--red); flex-shrink: 0; margin-top: 1px; }
        .error-box p { font-size: 12px; font-weight: 500; color: #f87171; }

        /* Submit button */
        .submit-wrap { padding: 24px; }
        .btn-submit {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            background: var(--amber);
            border: none;
            border-radius: 14px;
            color: #0b0f1a;
            font-size: 14px;
            font-weight: 800;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: -.01em;
            cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .15s;
            box-shadow: 0 4px 20px rgba(245,158,11,.25);
        }
        .btn-submit:hover:not(:disabled) {
            background: var(--amber-l);
            box-shadow: 0 6px 28px rgba(245,158,11,.35);
        }
        .btn-submit:active:not(:disabled) { transform: scale(.98); }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; }

        /* Spin */
        .spin {
            width: 16px; height: 16px;
            border: 2px solid rgba(0,0,0,.25);
            border-top-color: #0b0f1a;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        /* Contact footer */
        .contact-footer {
            margin-top: 20px;
            text-align: center;
            animation: fadeUp .5s ease both .25s;
        }
        .contact-footer p { font-size: 12px; color: var(--mut); margin-bottom: 4px; }
        .contact-footer a {
            font-size: 14px;
            font-weight: 700;
            color: var(--amber);
            text-decoration: none;
            transition: color .15s;
        }
        .contact-footer a:hover { color: var(--amber-l); }

        /* Animations */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body>
<div class="glow-top"></div>

<div class="page" x-data="reservationForm()" x-init="init()">

    <!-- Header -->
    <div class="header">
        <div class="logo-ring">
            @if($tenant->logo_url)
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}">
            @else
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
            @endif
        </div>
        <h1>{{ $tenant->name }}</h1>
        <p>Réservez votre table en quelques secondes</p>
    </div>

    <!-- Form card -->
    <div class="card">
        <form @submit.prevent="submitForm">

            <!-- Section 1 : Infos personnelles -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                    </div>
                    <span class="section-title">Vos informations</span>
                </div>

                <div class="field">
                    <label>Nom complet <span style="color:var(--amber);font-weight:700;">*</span></label>
                    <input type="text" x-model="form.customer_name" required placeholder="Votre nom complet">
                </div>
                <div class="field">
                    <label>Téléphone <span style="color:var(--amber);font-weight:700;">*</span></label>
                    <input type="tel" x-model="form.customer_phone" required placeholder="07 XX XX XX XX">
                </div>
                <div class="field">
                    <label>Email <span class="opt">(optionnel)</span></label>
                    <input type="email" x-model="form.customer_email" placeholder="votre@email.com">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Section 2 : Réservation -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                    </div>
                    <span class="section-title">Votre réservation</span>
                </div>

                <div class="field grid2">
                    <div>
                        <label>Date <span style="color:var(--amber);font-weight:700;">*</span></label>
                        <input type="date" x-model="form.reservation_date" required :min="minDate">
                    </div>
                    <div>
                        <label>Heure <span style="color:var(--amber);font-weight:700;">*</span></label>
                        <select x-model="form.reservation_time" required>
                            <option value="">Choisir…</option>
                            <template x-for="time in timeSlots" :key="time">
                                <option :value="time" x-text="time"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>Nombre de personnes <span style="color:var(--amber);font-weight:700;">*</span></label>
                    <div class="party-grid">
                        <template x-for="n in 10" :key="n">
                            <button type="button"
                                    @click="form.party_size = n"
                                    :class="form.party_size === n ? 'party-btn active' : 'party-btn'"
                                    x-text="n">
                            </button>
                        </template>
                    </div>
                </div>

                <div class="field">
                    <label>Demandes spéciales <span class="opt">(optionnel)</span></label>
                    <textarea x-model="form.special_requests" rows="3"
                              placeholder="Allergies, occasion spéciale, préférences de table…"></textarea>
                </div>
            </div>

            <!-- Error -->
            <div x-show="error" x-cloak class="error-box">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                </svg>
                <p x-text="error"></p>
            </div>

            <!-- Submit -->
            <div class="submit-wrap">
                <button type="submit" class="btn-submit" :disabled="loading">
                    <template x-if="loading">
                        <div class="spin"></div>
                    </template>
                    <span x-text="loading ? 'Réservation en cours…' : 'Confirmer la réservation'"></span>
                    <template x-if="!loading">
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </template>
                </button>
            </div>
        </form>
    </div>

    <!-- Contact -->
    @if($tenant->phone)
    <div class="contact-footer">
        <p>Des questions ? Appelez-nous</p>
        <a href="tel:{{ $tenant->phone }}">{{ $tenant->phone }}</a>
    </div>
    @endif

</div>

<script>
function reservationForm() {
    return {
        form: {
            customer_name: '',
            customer_phone: '',
            customer_email: '',
            reservation_date: '',
            reservation_time: '',
            party_size: 2,
            special_requests: ''
        },
        loading: false,
        error: '',
        minDate: new Date().toISOString().split('T')[0],
        timeSlots: [],

        init() {
            this.generateTimeSlots();
        },

        generateTimeSlots() {
            const slots = [];
            for (let h = 11; h <= 22; h++) {
                slots.push(`${h.toString().padStart(2, '0')}:00`);
                if (h < 22) slots.push(`${h.toString().padStart(2, '0')}:30`);
            }
            this.timeSlots = slots;
        },

        async submitForm() {
            this.loading = true;
            this.error = '';
            try {
                const response = await fetch('{{ route("reservation.store", $tenant->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    this.error = data.message || 'Une erreur est survenue.';
                }
            } catch (e) {
                this.error = 'Erreur de connexion. Veuillez réessayer.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
</body>
</html>
