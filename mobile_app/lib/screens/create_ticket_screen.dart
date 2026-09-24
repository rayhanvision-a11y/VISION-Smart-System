import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../services/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/common/primary_button.dart';

class CreateTicketScreen extends StatefulWidget {
  const CreateTicketScreen({Key? key}) : super(key: key);

  @override
  State<CreateTicketScreen> createState() => _CreateTicketScreenState();
}

class _CreateTicketScreenState extends State<CreateTicketScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descController = TextEditingController();

  String _selectedPriority = 'medium';
  String _selectedCategoryKey = 'line_fault';
  int? _selectedPopId;
  String? _selectedPopName;
  int? _selectedAssigneeId;
  String? _selectedAssigneeName;
  DateTime? _dueAt;
  final List<_PickedFile> _attachments = [];
  String? _selectedArea;

  bool _isLoading = false;
  bool _isLoadingMeta = true;
  List<String> _areas = [];

  List<dynamic> _categoriesApi = [];
  List<dynamic> _popOffices = [];
  List<dynamic> _staff = [];

  final Map<String, String> _fallbackCategories = {
    'line_fault': 'Line Fault / Link Down',
    'router_issue': 'Router / ONU Issue',
    'new_connection': 'New Connection',
    'billing': 'Billing / Payment',
    'other': 'Other Query',
  };

  @override
  void initState() {
    super.initState();
    _loadMeta();
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    super.dispose();
  }

  Future<void> _loadMeta() async {
    final results = await Future.wait([
      ApiService.getCategories(),
      ApiService.getPopOffices(),
      ApiService.getStaff(),
      ApiService.getAreas(),
    ]);
    if (!mounted) return;
    setState(() {
      _categoriesApi = results[0] as List;
      _popOffices = results[1] as List;
      _staff = results[2] as List;
      _areas = (results[3] as List).map((e) => e.toString()).toList();
      _isLoadingMeta = false;
    });
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    final res = await ApiService.createTicket(
      title: _titleController.text.trim(),
      description: _descController.text.trim(),
      priority: _selectedPriority,
      category: _selectedCategoryKey,
      popOfficeId: _selectedPopId,
      assignedTo: _selectedAssigneeId,
      dueAt: _dueAt,
      area: _selectedArea,
    );

    if (!mounted) return;

    if (res['success'] == true) {
      // Upload attachments if any
      if (_attachments.isNotEmpty && res['ticket'] != null) {
        final ticketId = res['ticket'].id as int;
        for (final a in _attachments) {
          try {
            await ApiService.uploadTicketAttachment(
              ticketId: ticketId,
              bytes: a.bytes,
              filename: a.name,
            );
          } catch (_) {}
        }
      }

      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: const [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
              SizedBox(width: 8),
              Text('Ticket created successfully'),
            ],
          ),
          backgroundColor: AppColors.success,
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pop(context, true);
    } else {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Failed to create ticket'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _showPicker<T>({
    required String title,
    required IconData icon,
    required List<T> items,
    required String Function(T) labelOf,
    required String Function(T) subtitleOf,
    required VoidCallback Function(T) onSelect,
  }) async {
    String search = '';
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setLocal) {
          final filtered = search.isEmpty
              ? items
              : items.where((i) =>
                  labelOf(i).toLowerCase().contains(search.toLowerCase()) ||
                  subtitleOf(i).toLowerCase().contains(search.toLowerCase())).toList();
          return DraggableScrollableSheet(
            initialChildSize: 0.7,
            maxChildSize: 0.9,
            minChildSize: 0.4,
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
                      decoration: BoxDecoration(
                        color: AppColors.border,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Row(
                    children: [
                      Icon(icon, color: AppColors.primary),
                      const SizedBox(width: 10),
                      Text(title, style: AppText.h2),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  TextField(
                    decoration: const InputDecoration(
                      hintText: 'Search…',
                      prefixIcon: Icon(Icons.search_rounded, size: 20),
                    ),
                    onChanged: (v) => setLocal(() => search = v),
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Expanded(
                    child: filtered.isEmpty
                        ? Center(
                            child: Text('No results', style: AppText.bodySm),
                          )
                        : ListView.separated(
                            controller: sc,
                            itemCount: filtered.length,
                            separatorBuilder: (_, __) => const Divider(height: 1),
                            itemBuilder: (_, i) {
                              final it = filtered[i];
                              return ListTile(
                                title: Text(labelOf(it), style: AppText.body),
                                subtitle: subtitleOf(it).isEmpty
                                    ? null
                                    : Text(subtitleOf(it), style: AppText.caption),
                                trailing: Icon(Icons.chevron_right_rounded, color: AppColors.textMuted),
                                onTap: () {
                                  onSelect(it).call();
                                  Navigator.pop(ctx);
                                },
                              );
                            },
                          ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final categoryLabel = _categoriesApi.isNotEmpty
        ? (_categoriesApi.firstWhere(
              (c) => c is Map && (c['slug']?.toString() == _selectedCategoryKey),
              orElse: () => null,
            )?['name']?.toString() ??
            _fallbackCategories[_selectedCategoryKey] ??
            'Select category')
        : (_fallbackCategories[_selectedCategoryKey] ?? 'Select category');

    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(title: Text(AppState.instance.t('New Ticket', 'নতুন টিকিট'))),
      body: _isLoadingMeta
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _sectionCard(
                      icon: Icons.category_rounded,
                      iconColor: AppColors.info,
                      title: AppState.instance.t('Category', 'ক্যাটেগরি'),
                      subtitle: categoryLabel,
                      onTap: () => _showCategoryPicker(),
                    ),
                    const SizedBox(height: 10),
                    _sectionCard(
                      icon: Icons.business_rounded,
                      iconColor: AppColors.success,
                      title: AppState.instance.t('POP Office (Optional)', 'পপ অফিস (ঐচ্ছিক)'),
                      subtitle: _selectedPopName ?? AppState.instance.t('Select POP office', 'পপ অফিস নির্বাচন করুন'),
                      onTap: _popOffices.isEmpty
                          ? null
                          : () => _showPicker(
                                title: 'Select POP Office',
                                icon: Icons.business_rounded,
                                items: _popOffices,
                                labelOf: (i) => (i as Map)['name']?.toString() ?? 'POP',
                                subtitleOf: (_) => '',
                                onSelect: (i) => () {
                                  final m = i as Map;
                                  setState(() {
                                    _selectedPopId = m['id'] as int?;
                                    _selectedPopName = m['name']?.toString();
                                  });
                                },
                              ),
                    ),
                    const SizedBox(height: 10),
                    _sectionCard(
                      icon: Icons.person_add_alt_rounded,
                      iconColor: AppColors.accent,
                      title: AppState.instance.t('Assign to Staff (Optional)', 'কর্মী নিয়োগ (ঐচ্ছিক)'),
                      subtitle: _selectedAssigneeName ?? AppState.instance.t('Unassigned', 'অনির্ধারিত'),
                      trailing: _selectedAssigneeName == null
                          ? null
                          : IconButton(
                              icon: Icon(Icons.close_rounded, size: 18, color: AppColors.textMuted),
                              onPressed: () => setState(() {
                                _selectedAssigneeId = null;
                                _selectedAssigneeName = null;
                              }),
                            ),
                      onTap: _staff.isEmpty
                          ? null
                          : () => _showPicker(
                                title: 'Assign Staff',
                                icon: Icons.person_add_alt_rounded,
                                items: _staff,
                                labelOf: (i) => (i as Map)['name']?.toString() ?? '—',
                                subtitleOf: (i) => (i as Map)['role']?.toString().replaceAll('_', ' ').toUpperCase() ?? '',
                                onSelect: (i) => () {
                                  final m = i as Map;
                                  setState(() {
                                    _selectedAssigneeId = m['id'] as int?;
                                    _selectedAssigneeName = m['name']?.toString();
                                  });
                                },
                              ),
                    ),
                    const SizedBox(height: 10),
                    _sectionCard(
                      icon: Icons.place_rounded,
                      iconColor: AppColors.danger,
                      title: AppState.instance.t('Area (Optional)', 'এলাকা (ঐচ্ছিক)'),
                      subtitle: _selectedArea ?? AppState.instance.t('Select or add new area', 'এলাকা নির্বাচন বা যোগ করুন'),
                      trailing: _selectedArea == null
                          ? null
                          : IconButton(
                              icon: Icon(Icons.close_rounded, size: 18, color: AppColors.textMuted),
                              onPressed: () => setState(() => _selectedArea = null),
                            ),
                      onTap: _showAreaPicker,
                    ),
                    const SizedBox(height: 10),
                    _sectionCard(
                      icon: Icons.event_rounded,
                      iconColor: AppColors.warning,
                      title: AppState.instance.t('Due Date (Optional)', 'শেষ তারিখ (ঐচ্ছিক)'),
                      subtitle: _dueAt != null
                          ? DateFormat('MMM d, y – h:mm a').format(_dueAt!)
                          : AppState.instance.t('No deadline set', 'কোনো ডেডলাইন নেই'),
                      trailing: _dueAt == null
                          ? null
                          : IconButton(
                              icon: Icon(Icons.close_rounded, size: 18, color: AppColors.textMuted),
                              onPressed: () => setState(() => _dueAt = null),
                            ),
                      onTap: _pickDueDate,
                    ),
                    const SizedBox(height: 10),
                    _sectionCard(
                      icon: Icons.attach_file_rounded,
                      iconColor: const Color(0xFF8B5CF6),
                      title: AppState.instance.t('Attachments (${_attachments.length})', 'সংযুক্তি (${_attachments.length})'),
                      subtitle: _attachments.isEmpty
                          ? AppState.instance.t('Add images or files', 'ছবি বা ফাইল যুক্ত করুন')
                          : _attachments.map((a) => a.name).join(', '),
                      onTap: _pickAttachment,
                    ),
                    if (_attachments.isNotEmpty) ...[
                      const SizedBox(height: 10),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: _attachments.asMap().entries.map((e) {
                          final i = e.key;
                          final a = e.value;
                          return Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withOpacity(0.08),
                              borderRadius: BorderRadius.circular(AppRadius.sm),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.image_outlined, size: 14, color: AppColors.primary),
                                const SizedBox(width: 4),
                                Text(a.name.length > 20 ? '${a.name.substring(0, 20)}…' : a.name,
                                    style: AppText.caption.copyWith(color: AppColors.primary)),
                                const SizedBox(width: 4),
                                InkWell(
                                  onTap: () => setState(() => _attachments.removeAt(i)),
                                  child: const Icon(Icons.close_rounded, size: 14, color: AppColors.primary),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    ],
                    const SizedBox(height: AppSpacing.lg),

                    Text(AppState.instance.t('Priority Level', 'অগ্রাধিকার'), style: AppText.label),
                    const SizedBox(height: 6),
                    _priorityRow(),
                    const SizedBox(height: AppSpacing.lg),

                    Text(AppState.instance.t('Ticket Title', 'টিকিট শিরোনাম'), style: AppText.label),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: _titleController,
                      decoration: InputDecoration(
                        hintText: AppState.instance.t('Brief summary of the issue', 'সমস্যার সংক্ষিপ্ত বিবরণ'),
                        prefixIcon: const Icon(Icons.title_rounded, size: 20),
                      ),
                      validator: (v) => (v == null || v.trim().isEmpty) ? AppState.instance.t('Title is required', 'শিরোনাম আবশ্যক') : null,
                    ),
                    const SizedBox(height: AppSpacing.md),

                    Text(AppState.instance.t('Detailed Description', 'বিস্তারিত বিবরণ'), style: AppText.label),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: _descController,
                      maxLines: 5,
                      minLines: 4,
                      decoration: InputDecoration(
                        hintText: AppState.instance.t('Provide complete details about the problem…', 'সমস্যার সম্পূর্ণ বিবরণ দিন…'),
                      ),
                      validator: (v) => (v == null || v.trim().isEmpty) ? AppState.instance.t('Description is required', 'বিবরণ আবশ্যক') : null,
                    ),

                    const SizedBox(height: AppSpacing.xl),
                    PrimaryButton(
                      label: AppState.instance.t('Create Ticket', 'টিকিট তৈরি'),
                      icon: Icons.send_rounded,
                      loading: _isLoading,
                      onPressed: _submit,
                    ),
                    const SizedBox(height: AppSpacing.md),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _sectionCard({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
    VoidCallback? onTap,
    Widget? trailing,
  }) {
    return Material(
      color: AppColors.card,
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: InkWell(
        borderRadius: BorderRadius.circular(AppRadius.lg),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.md),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: AppColors.border),
          ),
          child: Row(
            children: [
              Container(
                width: 40, height: 40,
                decoration: BoxDecoration(
                  color: iconColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Icon(icon, color: iconColor, size: 20),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: AppText.label),
                    const SizedBox(height: 2),
                    Text(subtitle,
                        style: AppText.body.copyWith(fontSize: 13),
                        maxLines: 1, overflow: TextOverflow.ellipsis),
                  ],
                ),
              ),
              trailing ?? Icon(Icons.chevron_right_rounded, color: AppColors.textMuted),
            ],
          ),
        ),
      ),
    );
  }

  Widget _priorityRow() {
    final options = ['low', 'medium', 'high', 'urgent'];
    return Row(
      children: options.map((p) {
        final selected = _selectedPriority == p;
        final color = AppColors.priorityColor(p);
        return Expanded(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 3),
            child: Material(
              color: selected ? color : AppColors.card,
              borderRadius: BorderRadius.circular(AppRadius.md),
              child: InkWell(
                borderRadius: BorderRadius.circular(AppRadius.md),
                onTap: () => setState(() => _selectedPriority = p),
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    border: Border.all(color: selected ? color : AppColors.border),
                  ),
                  child: Column(
                    children: [
                      Icon(
                        p == 'urgent' ? Icons.local_fire_department_rounded : Icons.flag_rounded,
                        size: 16,
                        color: selected ? Colors.white : color,
                      ),
                      const SizedBox(height: 4),
                      Text(p.toUpperCase(),
                          style: AppText.label.copyWith(
                            color: selected ? Colors.white : color,
                            fontWeight: FontWeight.w700,
                            fontSize: 10,
                          )),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  Future<void> _showAreaPicker() async {
    String search = '';
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setLocal) {
          final filtered = search.isEmpty
              ? _areas
              : _areas.where((a) => a.toLowerCase().contains(search.toLowerCase())).toList();
          final showAddNew = search.trim().isNotEmpty &&
              !_areas.any((a) => a.toLowerCase() == search.trim().toLowerCase());
          return DraggableScrollableSheet(
            initialChildSize: 0.7,
            maxChildSize: 0.9,
            minChildSize: 0.4,
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
                      decoration: BoxDecoration(
                        color: AppColors.border,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Row(
                    children: [
                      const Icon(Icons.place_rounded, color: AppColors.primary),
                      const SizedBox(width: 10),
                      Text(AppState.instance.t('Select Area', 'এলাকা নির্বাচন'), style: AppText.h2),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  TextField(
                    autofocus: true,
                    decoration: InputDecoration(
                      hintText: AppState.instance.t('Type area name (e.g. Shadhupara)', 'এলাকার নাম লিখুন'),
                      prefixIcon: const Icon(Icons.search_rounded, size: 20),
                    ),
                    onChanged: (v) => setLocal(() => search = v),
                  ),
                  const SizedBox(height: AppSpacing.sm),
                  if (showAddNew)
                    Material(
                      color: AppColors.accent.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(AppRadius.md),
                        onTap: () {
                          final v = search.trim();
                          setState(() {
                            _selectedArea = v;
                            if (!_areas.contains(v)) _areas.insert(0, v);
                          });
                          Navigator.pop(ctx);
                        },
                        child: Padding(
                          padding: const EdgeInsets.all(AppSpacing.md),
                          child: Row(
                            children: [
                              const Icon(Icons.add_circle_rounded, color: AppColors.accent),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  '${AppState.instance.t('Add new area', 'নতুন এলাকা যোগ')}: "$search"',
                                  style: AppText.body.copyWith(color: AppColors.accent, fontWeight: FontWeight.w600),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  const SizedBox(height: AppSpacing.sm),
                  Expanded(
                    child: filtered.isEmpty && !showAddNew
                        ? Center(
                            child: Text(AppState.instance.t('No areas yet — type to add', 'কোনো এলাকা নেই — লিখে যোগ করুন'), style: AppText.bodySm),
                          )
                        : ListView.separated(
                            controller: sc,
                            itemCount: filtered.length,
                            separatorBuilder: (_, __) => const Divider(height: 1),
                            itemBuilder: (_, i) {
                              final a = filtered[i];
                              return ListTile(
                                leading: Icon(Icons.place_outlined, color: AppColors.textMuted),
                                title: Text(a, style: AppText.body),
                                onTap: () {
                                  setState(() => _selectedArea = a);
                                  Navigator.pop(ctx);
                                },
                              );
                            },
                          ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _pickDueDate() async {
    final now = DateTime.now();
    final date = await showDatePicker(
      context: context,
      initialDate: _dueAt ?? now.add(const Duration(days: 1)),
      firstDate: now,
      lastDate: now.add(const Duration(days: 365)),
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_dueAt ?? now.add(const Duration(hours: 2))),
    );
    if (!mounted) return;
    setState(() {
      _dueAt = DateTime(date.year, date.month, date.day,
          time?.hour ?? 17, time?.minute ?? 0);
    });
  }

  Future<void> _pickAttachment() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85, maxWidth: 1600);
    if (picked == null || !mounted) return;
    final bytes = await picked.readAsBytes();
    setState(() => _attachments.add(_PickedFile(name: picked.name, bytes: bytes)));
  }

  void _showCategoryPicker() {
    final items = _categoriesApi.isNotEmpty
        ? _categoriesApi
        : _fallbackCategories.entries
            .map((e) => {'slug': e.key, 'name': e.value})
            .toList();

    _showPicker(
      title: 'Select Category',
      icon: Icons.category_rounded,
      items: items,
      labelOf: (i) => (i as Map)['name']?.toString() ?? '—',
      subtitleOf: (_) => '',
      onSelect: (i) => () {
        final m = i as Map;
        setState(() => _selectedCategoryKey = m['slug']?.toString() ?? 'other');
      },
    );
  }
}

class _PickedFile {
  final String name;
  final List<int> bytes;
  _PickedFile({required this.name, required this.bytes});
}
