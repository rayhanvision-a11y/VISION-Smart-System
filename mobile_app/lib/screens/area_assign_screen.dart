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
  List<String> _allAreas = [];
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
    final areas = await ApiService.getAreas();
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
          _areaSearchSection(),
          Expanded(
            child: _selectedArea == null
                ? EmptyState(
                    icon: Icons.place_rounded,
                    title: AppState.instance.t('Select an area', 'একটি এলাকা নির্বাচন করুন'),
                    subtitle: AppState.instance.t('Choose an area to see tickets and bulk-assign', 'এলাকা বাছুন → টিকিট দেখুন → নিয়োগ দিন'),
                  )
                : _ticketList(),
          ),
          if (_selectedIds.isNotEmpty) _actionBar(),
        ],
      ),
    );
  }

  Widget _areaSearchSection() {
    return Container(
      color: AppColors.card,
      padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.md),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextField(
            controller: _searchController,
            decoration: InputDecoration(
              hintText: AppState.instance.t('Type area name…', 'এলাকার নাম লিখুন…'),
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
            onChanged: (v) => setState(() {}),
          ),
          const SizedBox(height: AppSpacing.sm),
          if (_isLoadingAreas)
            const Skeleton(width: 200, height: 12)
          else
            SizedBox(
              height: 40,
              child: ListView(
                scrollDirection: Axis.horizontal,
                children: _filteredAreas().map((a) {
                  final selected = _selectedArea == a;
                  return Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: Material(
                      color: selected ? AppColors.primary : AppColors.bg,
                      borderRadius: BorderRadius.circular(AppRadius.pill),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                        onTap: () => _loadTicketsForArea(a),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                            border: Border.all(color: selected ? AppColors.primary : AppColors.border),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.place_rounded, size: 14,
                                  color: selected ? Colors.white : AppColors.primary),
                              const SizedBox(width: 4),
                              Text(a,
                                  style: AppText.caption.copyWith(
                                    color: selected ? Colors.white : AppColors.textPrimary,
                                    fontWeight: FontWeight.w600,
                                  )),
                            ],
                          ),
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
        ],
      ),
    );
  }

  List<String> _filteredAreas() {
    final q = _searchController.text.trim().toLowerCase();
    if (q.isEmpty) return _allAreas;
    return _allAreas.where((a) => a.toLowerCase().contains(q)).toList();
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
