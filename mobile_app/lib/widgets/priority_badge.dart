import 'package:flutter/material.dart';
import '../config/app_config.dart';

class PriorityBadge extends StatelessWidget {
  final String priority;

  const PriorityBadge({Key? key, required this.priority}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final color = AppConfig.priorityColor(priority);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.flag_rounded, size: 12, color: color),
          const SizedBox(width: 4),
          Text(
            priority.toUpperCase(),
            style: TextStyle(
              color: color,
              fontSize: 10,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}
