import 'package:flutter_test/flutter_test.dart';

void main() {
  test('test regex', () {
    final mentionRegex = RegExp(r'@([\p{L}][\p{L}\s\.]{1,30}?)(?=\s|$|[^\p{L}\s])', unicode: true);
    final matches = mentionRegex.allMatches('@admin hello').toList();
    print('Matches: ${matches.length}');
  });
}
