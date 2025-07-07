# 🤝 Contributing to EduLearn

Thank you for your interest in contributing to EduLearn! We welcome contributions from developers, educators, and anyone passionate about improving education through technology.

## 🎯 Ways to Contribute

### 🐛 **Bug Reports**
- Use our [issue template](https://github.com/Mohammed-ES/EduLearn/issues/new?template=bug_report.md)
- Include detailed steps to reproduce
- Provide screenshots when applicable

### ✨ **Feature Requests**
- Check existing [feature requests](https://github.com/Mohammed-ES/EduLearn/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)
- Use our [feature request template](https://github.com/Mohammed-ES/EduLearn/issues/new?template=feature_request.md)
- Describe the problem and proposed solution

### 💻 **Code Contributions**
- Fork the repository
- Create a feature branch
- Follow our coding standards
- Submit a pull request

## 🛠️ Development Setup

### Prerequisites
- PHP 8.0+
- MySQL 8.0+
- Node.js (for build tools)
- Git

### Local Setup
```bash
git clone https://github.com/Mohammed-ES/EduLearn.git
cd EduLearn
cp .env.example .env
# Edit .env with your configuration
mysql -u root -p < database/edulearn_db.sql
```

## 📝 Coding Standards

### PHP Standards
- Follow PSR-4 autoloading standards
- Use meaningful variable and function names
- Add PHPDoc comments for all functions
- Use prepared statements for database queries

### JavaScript Standards
- Use ES6+ features
- Follow camelCase naming convention
- Add JSDoc comments for functions
- Use async/await for asynchronous operations

### CSS Standards
- Use BEM methodology
- Mobile-first responsive design
- Use CSS custom properties for theming
- Optimize for performance

## 🧪 Testing

### Before Submitting
- [ ] Test all functionality manually
- [ ] Check for console errors
- [ ] Verify mobile responsiveness
- [ ] Test with different browsers
- [ ] Validate HTML/CSS
- [ ] Check accessibility compliance

## 📋 Pull Request Process

1. **Update Documentation**: Include relevant documentation updates
2. **Add Tests**: Include tests for new features
3. **Check Security**: Ensure no sensitive data is exposed
4. **Performance**: Verify no performance regressions
5. **Review**: Request review from maintainers

## 🏷️ Commit Message Guidelines

```
type(scope): description

Examples:
feat(quiz): add AI-powered question generation
fix(auth): resolve login session timeout issue
docs(readme): update installation instructions
style(css): improve mobile responsive design
```

## 🌟 Recognition

Contributors will be:
- Listed in our README contributors section
- Acknowledged in release notes
- Invited to our contributor Discord server

## 📞 Getting Help

- 💬 **Discord**: [Join our community](https://discord.gg/edulearn)
- 📧 **Email**: contribute@edulearn.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/Mohammed-ES/EduLearn/issues)

## 📄 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

**Thank you for making EduLearn better! 🎉**
