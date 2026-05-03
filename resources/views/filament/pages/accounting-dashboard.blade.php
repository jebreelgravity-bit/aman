<x-filament-panels::page>

    {{-- ─── فلاتر الفترة ─── --}}
    <div class="flex flex-wrap gap-3 mb-6 p-4 bg-gray-900 rounded-xl border border-gray-700">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-400 font-medium">السنة:</label>
            <select wire:model.live="selectedYear"
                    class="bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-yellow-500">
                @foreach(range(now()->year, now()->year - 4) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-400 font-medium">الشهر:</label>
            <select wire:model.live="selectedMonth"
                    class="bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-yellow-500">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}">{{ $this->getMonthName($m) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-1 ms-auto">
            <span class="text-xs text-gray-500">عرض بيانات:</span>
            <span class="text-sm font-bold text-yellow-400">{{ $this->getMonthName($selectedMonth) }} {{ $selectedYear }}</span>
        </div>
    </div>

    {{-- ─── بطاقات الملخص ─── --}}
    @php $summary = $this->getFinancialSummary(); @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        {{-- الإيرادات --}}
        <div class="col-span-2 md:col-span-1 lg:col-span-1 bg-gradient-to-br from-emerald-900 to-emerald-800 rounded-xl p-4 border border-emerald-700 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-emerald-300">إيرادات الرحلات</span>
                <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-5 h-5 text-emerald-400"/>
            </div>
            <div class="text-2xl font-bold text-white">{{ number_format($summary['trip_revenue'], 0) }}</div>
            <div class="text-xs text-emerald-300 mt-1">ريال</div>
        </div>

        {{-- المصروفات --}}
        <div class="col-span-2 md:col-span-1 lg:col-span-1 bg-gradient-to-br from-red-900 to-red-800 rounded-xl p-4 border border-red-700 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-red-300">إجمالي المصروفات</span>
                <x-filament::icon icon="heroicon-o-arrow-trending-down" class="w-5 h-5 text-red-400"/>
            </div>
            <div class="text-2xl font-bold text-white">{{ number_format($summary['total_expenses'], 0) }}</div>
            <div class="text-xs text-red-300 mt-1">ريال</div>
        </div>

        {{-- صافي الربح --}}
        <div class="col-span-2 md:col-span-1 lg:col-span-1 bg-gradient-to-br from-{{ $summary['net_profit'] >= 0 ? 'blue-900 to-blue-800 border-blue-700' : 'orange-900 to-orange-800 border-orange-700' }} rounded-xl p-4 border shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-{{ $summary['net_profit'] >= 0 ? 'blue' : 'orange' }}-300">صافي الربح</span>
                <x-filament::icon icon="heroicon-o-scale" class="w-5 h-5 text-{{ $summary['net_profit'] >= 0 ? 'blue' : 'orange' }}-400"/>
            </div>
            <div class="text-2xl font-bold text-white">{{ number_format(abs($summary['net_profit']), 0) }}</div>
            <div class="text-xs text-{{ $summary['net_profit'] >= 0 ? 'blue' : 'orange' }}-300 mt-1">
                {{ $summary['net_profit'] >= 0 ? 'ربح' : 'خسارة' }}
            </div>
        </div>

        {{-- معلّق --}}
        <div class="col-span-2 md:col-span-1 lg:col-span-1 bg-gradient-to-br from-yellow-900 to-yellow-800 rounded-xl p-4 border border-yellow-700 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-yellow-300">مصروفات معلقة</span>
                <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-yellow-400"/>
            </div>
            <div class="text-2xl font-bold text-white">{{ $summary['pending_count'] }}</div>
            <div class="text-xs text-yellow-300 mt-1">بانتظار الموافقة</div>
        </div>

        {{-- الرصيد البنكي --}}
        <div class="col-span-2 md:col-span-1 lg:col-span-1 bg-gradient-to-br from-purple-900 to-purple-800 rounded-xl p-4 border border-purple-700 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-purple-300">الرصيد البنكي</span>
                <x-filament::icon icon="heroicon-o-building-library" class="w-5 h-5 text-purple-400"/>
            </div>
            <div class="text-2xl font-bold text-white">{{ number_format($summary['bank_balance'], 0) }}</div>
            <div class="text-xs text-purple-300 mt-1">ريال</div>
        </div>

        {{-- نسبة المصروفات --}}
        <div class="col-span-2 md:col-span-1 lg:col-span-1 bg-gradient-to-br from-gray-800 to-gray-700 rounded-xl p-4 border border-gray-600 shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-300">نسبة الإنفاق</span>
                <x-filament::icon icon="heroicon-o-chart-pie" class="w-5 h-5 text-gray-400"/>
            </div>
            @php $ratio = $summary['trip_revenue'] > 0 ? round(($summary['total_expenses'] / $summary['trip_revenue']) * 100) : 0; @endphp
            <div class="text-2xl font-bold text-white">{{ $ratio }}%</div>
            <div class="text-xs text-gray-300 mt-1">من الإيرادات</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- ─── مقارنة الميزانية ─── --}}
        <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5 text-yellow-400"/>
                    الميزانية مقابل الفعلي
                </h3>
                <a href="{{ route('filament.admin.resources.expenses.index') }}"
                   class="text-xs text-yellow-400 hover:text-yellow-300 flex items-center gap-1">
                    عرض المصروفات
                    <x-filament::icon icon="heroicon-o-arrow-left" class="w-3 h-3"/>
                </a>
            </div>

            @php $budgets = $this->getBudgetComparison(); @endphp

            @if(count($budgets) === 0)
                <div class="text-center py-8 text-gray-500">
                    <x-filament::icon icon="heroicon-o-inbox" class="w-10 h-10 mx-auto mb-2 opacity-50"/>
                    <p class="text-sm">لا توجد ميزانيات محددة لهذه الفترة</p>
                    <a href="{{ route('filament.admin.resources.budgets.create') }}" class="text-yellow-400 text-xs hover:underline mt-1 block">
                        + إضافة ميزانية
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($budgets as $item)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-300 font-medium">{{ $item['name'] }}</span>
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="text-gray-400">{{ number_format($item['spent'], 0) }} / {{ number_format($item['budgeted'], 0) }}</span>
                                    <span class="font-bold {{ $item['over'] ? 'text-red-400' : ($item['percentage'] >= 80 ? 'text-yellow-400' : 'text-emerald-400') }}">
                                        {{ $item['percentage'] }}%
                                    </span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-500
                                    {{ $item['over'] ? 'bg-red-500' : ($item['percentage'] >= 80 ? 'bg-yellow-500' : 'bg-emerald-500') }}"
                                     style="width: {{ min($item['percentage'], 100) }}%">
                                </div>
                            </div>
                            @if($item['over'])
                                <p class="text-xs text-red-400 mt-0.5">⚠ تجاوز الميزانية بـ {{ number_format($item['spent'] - $item['budgeted'], 0) }} ريال</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─── الحسابات البنكية ─── --}}
        <div class="bg-gray-900 rounded-xl border border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-building-library" class="w-5 h-5 text-purple-400"/>
                    الحسابات البنكية
                </h3>
                <a href="{{ route('filament.admin.resources.bank-accounts.index') }}"
                   class="text-xs text-purple-400 hover:text-purple-300">إدارة</a>
            </div>

            @php $bankAccounts = $this->getBankAccounts(); @endphp

            @if($bankAccounts->isEmpty())
                <div class="text-center py-6 text-gray-500">
                    <x-filament::icon icon="heroicon-o-building-library" class="w-8 h-8 mx-auto mb-2 opacity-40"/>
                    <p class="text-xs">لا توجد حسابات بنكية</p>
                    <a href="{{ route('filament.admin.resources.bank-accounts.create') }}" class="text-purple-400 text-xs hover:underline">
                        + إضافة حساب
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($bankAccounts as $account)
                        <div class="bg-gray-800 rounded-lg p-3 border border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-white">{{ $account->bank_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $account->account_name }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $account->account_number }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-emerald-400">{{ number_format($account->current_balance, 0) }}</p>
                                    <p class="text-xs text-gray-400">{{ $account->currency }}</p>
                                    @if($account->is_primary)
                                        <span class="text-xs bg-purple-900 text-purple-300 rounded px-1">رئيسي</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="border-t border-gray-700 pt-2 mt-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">الإجمالي:</span>
                            <span class="font-bold text-white">{{ number_format($bankAccounts->sum('current_balance'), 0) }} ريال</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ─── المصروفات المعلقة ─── --}}
        <div class="bg-gray-900 rounded-xl border border-yellow-700/50 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-yellow-400"/>
                    مصروفات بانتظار الموافقة
                    @if($summary['pending_count'] > 0)
                        <span class="bg-yellow-500 text-black text-xs font-bold rounded-full px-2 py-0.5">{{ $summary['pending_count'] }}</span>
                    @endif
                </h3>
                <a href="{{ route('filament.admin.resources.expenses.index', ['tableFilters[status][value]' => 'pending']) }}"
                   class="text-xs text-yellow-400 hover:text-yellow-300">عرض الكل</a>
            </div>

            @php $pendingList = $this->getPendingExpenses(); @endphp

            @if($pendingList->isEmpty())
                <div class="text-center py-6 text-gray-500">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-10 h-10 mx-auto mb-2 text-emerald-600"/>
                    <p class="text-sm text-emerald-400">لا توجد مصروفات معلقة 🎉</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($pendingList as $expense)
                        <div class="flex items-center justify-between bg-gray-800 rounded-lg p-3 border border-yellow-700/30">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0"></div>
                                <div class="min-w-0">
                                    <p class="text-sm text-white truncate font-medium">{{ $expense->description }}</p>
                                    <p class="text-xs text-gray-400">{{ $expense->category?->name }} • {{ $expense->paidBy?->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-sm font-bold text-yellow-400">{{ number_format($expense->amount, 0) }}</span>
                                <div class="flex gap-1">
                                    <a href="{{ route('filament.admin.resources.expenses.edit', $expense->id) }}"
                                       class="bg-emerald-700 hover:bg-emerald-600 text-white text-xs px-2 py-1 rounded">
                                        موافقة
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─── آخر المصروفات ─── --}}
        <div class="bg-gray-900 rounded-xl border border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-red-400"/>
                    آخر المصروفات
                </h3>
                <a href="{{ route('filament.admin.resources.expenses.create') }}"
                   class="text-xs bg-red-900 hover:bg-red-800 text-red-300 px-3 py-1 rounded-lg">+ إضافة مصروف</a>
            </div>

            @php $recentExpenses = $this->getRecentExpenses(); @endphp

            @if($recentExpenses->isEmpty())
                <div class="text-center py-6 text-gray-500">
                    <p class="text-sm">لا توجد مصروفات مسجلة بعد</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($recentExpenses as $expense)
                        <div class="flex items-center justify-between bg-gray-800 rounded-lg p-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                     style="background-color: {{ $expense->category?->color ?? '#6b7280' }}22; color: {{ $expense->category?->color ?? '#6b7280' }}">
                                    {{ mb_substr($expense->category?->name ?? '؟', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-white truncate">{{ $expense->description }}</p>
                                    <p class="text-xs text-gray-400">{{ $expense->expense_date?->format('Y-m-d') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-sm font-bold {{ $expense->status === 'paid' ? 'text-emerald-400' : ($expense->status === 'pending' ? 'text-yellow-400' : ($expense->status === 'rejected' ? 'text-red-400' : 'text-blue-400')) }}">
                                    {{ number_format($expense->amount, 0) }}
                                </span>
                                <span class="text-xs px-2 py-0.5 rounded {{ match($expense->status) {
                                    'pending'  => 'bg-yellow-900 text-yellow-300',
                                    'approved' => 'bg-blue-900 text-blue-300',
                                    'rejected' => 'bg-red-900 text-red-300',
                                    'paid'     => 'bg-emerald-900 text-emerald-300',
                                    default    => 'bg-gray-700 text-gray-300',
                                } }}">
                                    {{ match($expense->status) {
                                        'pending'  => 'معلّق',
                                        'approved' => 'موافق',
                                        'rejected' => 'مرفوض',
                                        'paid'     => 'مدفوع',
                                        default    => $expense->status,
                                    } }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ─── روابط سريعة ─── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        <a href="{{ route('filament.admin.resources.expenses.create') }}"
           class="bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-xl p-4 text-center transition-colors group">
            <x-filament::icon icon="heroicon-o-plus-circle" class="w-8 h-8 mx-auto text-emerald-400 group-hover:scale-110 transition-transform mb-2"/>
            <p class="text-sm font-medium text-white">إضافة مصروف</p>
        </a>

        <a href="{{ route('filament.admin.resources.budgets.create') }}"
           class="bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-xl p-4 text-center transition-colors group">
            <x-filament::icon icon="heroicon-o-chart-pie" class="w-8 h-8 mx-auto text-blue-400 group-hover:scale-110 transition-transform mb-2"/>
            <p class="text-sm font-medium text-white">تخصيص ميزانية</p>
        </a>

        <a href="{{ route('filament.admin.resources.bank-accounts.create') }}"
           class="bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-xl p-4 text-center transition-colors group">
            <x-filament::icon icon="heroicon-o-building-library" class="w-8 h-8 mx-auto text-purple-400 group-hover:scale-110 transition-transform mb-2"/>
            <p class="text-sm font-medium text-white">إضافة حساب بنكي</p>
        </a>

        <a href="{{ route('filament.admin.resources.budget-categories.create') }}"
           class="bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-xl p-4 text-center transition-colors group">
            <x-filament::icon icon="heroicon-o-tag" class="w-8 h-8 mx-auto text-yellow-400 group-hover:scale-110 transition-transform mb-2"/>
            <p class="text-sm font-medium text-white">إدارة الفئات</p>
        </a>
    </div>

</x-filament-panels::page>
