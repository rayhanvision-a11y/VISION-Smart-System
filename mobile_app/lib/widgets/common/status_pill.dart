import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class StatusPill extends StatelessWidget {
  final String status;
  final bool solid;
  const StatusPill({Key? key, required this.status, this.solid = false}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final c = AppColors.statusColor(status);
    final label = status.replaceAll('_', ' ').toUpperCase();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: solid ? c : c.withOpacity(0.12),
        borderRadius: BorderRadius.circular(AppRadius.sm),
      ),
      child: Text(
        label,
        style: AppText.label.copyWith(
          color: solid ? Colors.white : c,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

class PriorityPill extends StatelessWidget {
  final String priority;
  const PriorityPill({Key? key, required this.priority}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final c = AppColors.priorityColor(priority);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withOpacity(0.12),
        borderRadius: BorderRadius.circular(AppRadius.sm),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.flag_rounded, size: 11, color: c),
          const SizedBox(width: 4),
          Text(priority.toUpperCase(), style: AppText.label.copyWith(color: c, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}
