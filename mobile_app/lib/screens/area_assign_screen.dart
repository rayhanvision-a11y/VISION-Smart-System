import 'package:flutter/material.dart';
import '../models/ticket.dart';
import '../services/api_service.dart';
import '../services/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/common/empty_state.dart';
import '../widgets/common/primary_button.dart';
import '../widgets/common/skeleton.dart';
import '../widgets/common/status_pill.dart';

class AreaAssignScreen extends StatefulWidget {
  const AreaAssignScreen({Key? key}) : super(key: key);

  @override
  State<AreaAssignScreen> createState() => _AreaAssignScreenState();
}

class _AreaAssignScreenState extends State<AreaAssignScreen> {
  final _searchController = TextEditingController();
  List<Map<String, dynamic>> _allAreas = [];
  String? _selectedArea;
  bool _isLoadingAreas = true;
  bool _isLoadingTickets = false;
  bool _isAssigning = false;
  List<TicketModel> _tickets = [];
  final Set<int> _selectedIds = {};

  @override
  void initState() {
    super.initState();
    _loadAreas();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadAreas() async {
    final areas = await ApiService.getAreasWithCounts();
    if (!mounted) return;
    setState(() {
      _allAreas = areas;
      _isLoadingAreas = false;
    });
  }

  Future<void> _loadTicketsForArea(String area) async {
    setState(() {
      _selectedArea = area;
      _isLoadingTickets = true;
      _selectedIds.clear();
    });
    final tickets = await ApiService.fetchTickets(area: area);
    if (!mounted) return;
    setState(() {
      _tickets = tickets;
      _isLoadingTickets = false;
    });
  }

  Future<void> _showTechnicianPicker() async {
    final technicians = await ApiService.getTechnicians();
    if (!mounted) return;
    if (technicians.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppState.instance.t('No technicians available', 'কোনো টেকনিশিয়ান নেই')),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.6,
        expand: false,
        builder: (_, sc) => Container(
          decoration: BoxDecoration(
            color: AppColors.card,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
          ),
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, 0),
          child: Column(
            children: [
              Center(
                child: Container(
                  width: 40, height: 4,
                  decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2)),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              Row(
                children: [
                  const Icon(Icons.engineering_rounded, color: AppColors.primary),
                  const SizedBox(width: 10),
                  Text(AppState.instance.t('Assign to Technician', 'টেকনিশিয়ানকে দিন'), style: AppText.h2),
                  const Spacer(),
                  Text('${_selectedIds.length}',
                      style: AppText.h3.copyWith(color: AppColors.accent)),
                ],
              ),
              const SizedBox(height: AppSpacing.sm),
              Text(AppState.instance.t('Sorted by current workload', 'কাজের চাপ অনুসারে'),
                  style: AppText.caption),
              const SizedBox(height: AppSpacing.md),
              Expanded(
                child: ListView.separated(
                  controller: sc,
                  itemCount: technicians.length,
                  separatorBuilder: (_, __) => const Divider(height: 1),
                  itemBuilder: (_, i) {
                    final t = technicians[i] as Map;
                    final count = (t['active_count'] ?? 0) as int;
                    return ListTile(
                      leading: CircleAvatar(
                        backgroundColor: AppColors.primary.withOpacity(0.1),
                        child: Text(
                          (t['name']?.toString() ?? '?')[0].toUpperCase(),
                          style: AppText.h3.copyWith(color: AppColors.primary),
                        ),
                      ),
                      title: Text(t['name']?.toString() ?? '—', style: AppText.body),
                      subtitle: Text(
                        '${count} ${AppState.instance.t('active tickets', 'সক্রিয় টিকিট')}',
                        style: AppText.caption,
                      ),
                      trailing: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: count > 10
                              ? AppColors.danger.withOpacity(0.12)
                              : count > 5
                                  ? AppColors.warning.withOpacity(0.12)
                                  : AppColors.success.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                        ),
                        child: Text('$count',
                            style: AppText.label.copyWith(
                              color: count > 10
                                  ? AppColors.danger
                                  : count > 5
                                      ? AppColors.warning
                                      : AppColors.success,
                            )),
                      ),
                      onTap: () async {
                        Navigator.pop(ctx);
                        await _performAssign(t['id'] as int, t['name']?.toString() ?? 'Technician');
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _performAssign(int technicianId, String technicianName) async {
    setState(() => _isAssigning = true);
    final res = await ApiService.bulkAssign(
      ticketIds: _selectedIds.toList(),
      assignedTo: technicianId,
    );
    if (!mounted) return;
    setState(() => _isAssigning = false);
    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${res['count']} ${AppState.instance.t('tickets assigned to', 'টি টিকিট দেওয়া হয়েছে')} $technicianName ✓'),
          backgroundColor: AppColors.success,
          behavior: SnackBarBehavior.floating,
        ),
      );
      _selectedIds.clear();
      if (_selectedArea != null) _loadTicketsForArea(_selectedArea!);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message']?.toString() ?? 'Failed'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        title: Text(AppState.instance.t('Area Assign', 'এলাকা অনুযায়ী নিয়োগ')),
      ),
      body: Column(
        children: [
          _searchHeader(),
          if (_selectedArea != null) _selectedAreaBar(),
          Expanded(
            child: _selectedArea == null
                ? _areaListView()
                : _ticketList(),
          ),
          if (_selectedIds.isNotEmpty) _actionBar(),
        ],
      ),
    );
  }

  Widget _searchHeader() {
    return Container(
      color: AppColors.card,
      padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.md),
      child: TextField(
        controller: _searchController,
        decoration: InputDecoration(
          hintText: AppState.instance.t('Search area…', 'এলাকা খুঁজুন…'),
          prefixIcon: const Icon(Icons.search_rounded, size: 20),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.close_rounded, size: 18),
                  onPressed: () {
                    _searchController.clear();
                    setState(() {});
                  },
                )
              : null,
        ),
        onChanged: (_) => setState(() {}),
      ),
    );
  }

  Widget _selectedAreaBar() {
    return Container(
      color: AppColors.primary.withOpacity(0.06),
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: 10),
      child: Row(
        children: [
          const Icon(Icons.place_rounded, color: AppColors.primary, size: 18),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              _selectedArea!,
              style: AppText.body.copyWith(color: AppColors.primary, fontWeight: FontWeight.w700),
            ),
          ),
          TextButton.icon(
            onPressed: () => setState(() {
              _selectedArea = null;
              _tickets = [];
              _selectedIds.clear();
            }),
            icon: const Icon(Icons.arrow_back_rounded, size: 16),
            label: Text(AppState.instance.t('Areas', 'এলাকা')),
          ),
        ],
      ),
    );
  }

  Widget _areaListView() {
    if (_isLoadingAreas) {
      return ListView.separated(
        padding: const EdgeInsets.all(AppSpacing.lg),
        itemCount: 6,
        separatorBuilder: (_, __) => const SizedBox(height: 8),
        itemBuilder: (_, __) => const SkeletonCard(height: 64),
      );
    }
    final filtered = _filteredAreas();
    if (filtered.isEmpty) {
      return EmptyState(
        icon: Icons.place_outlined,
        title: AppState.instance.t('No areas found', 'কোনো এলাকা নেই'),
        subtitle: AppState.instance.t('Add areas from Settings → Manage Areas', 'সেটিংস → Manage Areas এ যোগ করুন'),
      );
    }
    return RefreshIndicator(
      color: AppColors.primary,
      onRefresh: _loadAreas,
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.xxl),
        itemCount: filtered.length,
        separatorBuilder: (_, __) => const SizedBox(height: 8),
        itemBuilder: (_, i) => _areaCard(filtered[i], i),
      ),
    );
  }

  Widget _areaCard(Map<String, dynamic> a, int rank) {
    final name = a['name']?.toString() ?? '—';
    final count = (a['active_count'] as int?) ?? 0;
    final isTop = rank == 0 && count > 0;
    final busy = count > 10;
    final medium = count > 3 && count <= 10;
    final Color badgeColor = count == 0
        ? AppColors.textMuted
        : busy
            ? AppColors.danger
            : medium
                ? AppColors.warning
                : AppColors.success;

    return Material(
      color: isTop ? AppColors.accent.withOpacity(0.08) : AppColors.card,
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.lg),
        onTap: () => _loadTicketsForArea(name),
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.md),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: isTop ? AppColors.accent : AppColors.border),
            boxShadow: AppShadows.card,
          ),
          child: Row(
            children: [
              Container(
                width: 44, height: 44,
                decoration: BoxDecoration(
                  gradient: isTop ? AppColors.goldGradient : null,
                  color: isTop ? null : AppColors.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: Icon(Icons.place_rounded, size: 22, color: isTop ? Colors.white : AppColors.primary),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        if (isTop) ...[
                          const Text('🔥 ', style: TextStyle(fontSize: 14)),
                        ],
                        Flexible(
                          child: Text(name,
                              style: AppText.body.copyWith(fontWeight: FontWeight.w700),
                              maxLines: 1, overflow: TextOverflow.ellipsis),
                        ),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      count == 0
                          ? AppState.instance.t('No active tickets', 'কোনো সক্রিয় টিকিট নেই')
                          : '$count ${AppState.instance.t('active tickets', 'সক্রিয় টিকিট')}',
                      style: AppText.caption.copyWith(fontSize: 11),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: badgeColor.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(AppRadius.pill),
                ),
                child: Text('$count',
                    style: AppText.h3.copyWith(color: badgeColor, fontSize: 16)),
              ),
              const SizedBox(width: 4),
              Icon(Icons.chevron_right_rounded, color: AppColors.textMuted),
            ],
          ),
        ),
      ),
    );
  }

  List<Map<String, dynamic>> _filteredAreas() {
    final q = _searchController.text.trim().toLowerCase();
    if (q.isEmpty) return _allAreas;
    return _allAreas.where((a) =>
      (a['name']?.toString().toLowerCase() ?? '').contains(q)).toList();
  }

  Widget _ticketList() {
    if (_isLoadingTickets) {
      return ListView.separated(
        padding: const EdgeInsets.all(AppSpacing.lg),
        itemCount: 4,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (_, __) => const SkeletonCard(height: 80),
      );
    }
    if (_tickets.isEmpty) {
      return EmptyState(
        icon: Icons.inbox_rounded,
        title: AppState.instance.t('No tickets in this area', 'এই এলাকায় কোনো টিকিট নেই'),
      );
    }
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, 0),
          child: Row(
            children: [
              Text('${_tickets.length} ${AppState.instance.t('tickets in', 'টিকিট')} $_selectedArea',
                  style: AppText.body.copyWith(fontWeight: FontWeight.w600)),
              const Spacer(),
              TextButton.icon(
                onPressed: () {
                  setState(() {
                    if (_selectedIds.length == _tickets.length) {
                      _selectedIds.clear();
                    } else {
                      _selectedIds.addAll(_tickets.map((t) => t.id));
                    }
                  });
                },
                icon: Icon(_selectedIds.length == _tickets.length
                    ? Icons.deselect_rounded
                    : Icons.select_all_rounded, size: 18),
                label: Text(
                  _selectedIds.length == _tickets.length
                      ? AppState.instance.t('Clear', 'বাতিল')
                      : AppState.instance.t('Select All', 'সব নির্বাচন'),
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: ListView.separated(
            padding: const EdgeInsets.fromLTRB(AppSpacing.lg, 4, AppSpacing.lg, 120),
            itemCount: _tickets.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (_, i) => _ticketRow(_tickets[i]),
          ),
        ),
      ],
    );
  }

  Widget _ticketRow(TicketModel t) {
    final selected = _selectedIds.contains(t.id);
    final pColor = AppColors.priorityColor(t.priority);
    return Material(
      color: selected ? AppColors.primary.withOpacity(0.06) : AppColors.card,
      borderRadius: BorderRadius.circular(AppRadius.md),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.md),
        onTap: () => setState(() {
          selected ? _selectedIds.remove(t.id) : _selectedIds.add(t.id);
        }),
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.md),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppRadius.md),
            border: Border.all(color: selected ? AppColors.primary : AppColors.border),
          ),
          child: Row(
            children: [
              Icon(
                selected ? Icons.check_box_rounded : Icons.check_box_outline_blank_rounded,
                color: selected ? AppColors.primary : AppColors.textMuted,
              ),
              const SizedBox(width: 10),
              Container(
                width: 4, height: 40,
                decoration: BoxDecoration(color: pColor, borderRadius: BorderRadius.circular(2)),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text('#${t.ticketKey}',
                            style: AppText.label.copyWith(color: AppColors.textMuted)),
                        const SizedBox(width: 6),
                        StatusPill(status: t.status),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(t.title,
                        style: AppText.body.copyWith(fontSize: 13),
                        maxLines: 1, overflow: TextOverflow.ellipsis),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _actionBar() {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.card,
        border: Border(top: BorderSide(color: AppColors.border)),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 12, offset: const Offset(0, -2)),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.1),
                borderRadius: BorderRadius.circular(AppRadius.md),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.check_circle_rounded, color: AppColors.primary, size: 18),
                  const SizedBox(width: 6),
                  Text('${_selectedIds.length}',
                      style: AppText.h3.copyWith(color: AppColors.primary)),
                  const SizedBox(width: 4),
                  Text(AppState.instance.t('selected', 'নির্বাচিত'),
                      style: AppText.caption.copyWith(color: AppColors.primary)),
                ],
              ),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: PrimaryButton(
                label: AppState.instance.t('Assign to Technician', 'টেকনিশিয়ানকে দিন'),
                icon: Icons.engineering_rounded,
                kind: PrimaryButtonKind.gold,
                loading: _isAssigning,
                onPressed: _showTechnicianPicker,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
