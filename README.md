# AutoSEO Pro

> 全自动 AI SEO 优化插件，为 WordPress 网站提供智能化 SEO 解决方案。

**版本**：2.0.0  
**作者**：SunnyWP  
**要求**：WordPress 6.0+，PHP 8.0+  
**官网**：[sunnywp.com](https://sunnywp.com)

---

## 功能特性

### 🤖 AI 内容生成
- **AI 标题生成** — 根据文章正文自动生成 5 个高点击率 SEO 标题，支持一键应用
- **AI 元描述生成** — 自动生成符合 SEO 规范的 Meta Description（120-160 字符）
- **AI 聚焦关键词提取** — 智能分析文章内容，提取最核心的 SEO 关键词
- **AI 社交媒体文案** — 自动生成适合微博、微信分享的文案（含 Emoji 和话题标签）

### 📊 SEO 分析与评分
- 实时 SEO 评分（0-100 分），可视化评分环
- 自动检测标题长度、元描述、H1 标签、图片 ALT、关键词密度、内部链接等
- 问题分级提示（错误 / 警告 / 建议）

### 🔧 一键修复
- 自动修复缺失的图片 ALT 属性
- 优化 H 标签层级结构
- 检测并提示重复内容问题

### 📱 社交媒体优化
- 自动输出 Open Graph 标签（og:title / og:description / og:image / og:site_name）
- Twitter Card 标签注入
- 支持每篇文章单独设置 OG 标题和分享文案
- 无封面图时自动使用全站默认 OG 图片兜底

### 🔗 社交分享栏
- 自动在文章正文末尾注入分享按钮
- 支持微博、微信、X（Twitter）、LinkedIn、复制链接
- 分享文案优先使用 AI 生成的社交文案
- 不依赖主题，适用于任何 WordPress 主题

### 📈 关键词排名监控
- 支持 Google Custom Search API 查询关键词排名
- 支持百度站长链接提交 Token
- 定时自动检查，排名变化邮件通知

### 🏗 技术 SEO
- JSON-LD 结构化数据注入（Article / NewsArticle / BlogPosting / TechArticle / WebPage）
- XML Sitemap 自动生成
- 面包屑导航 Shortcode（`[autoseo_breadcrumb]`）
- 自定义 Canonical URL
- 页面级 noindex / robots 控制
- 301 重定向设置

---

## 支持的 AI 服务商

| 服务商 | 备注 |
|---|---|
| OpenAI | GPT-4o、GPT-4o-mini 等 |
| Claude (Anthropic) | Claude 3.5 Sonnet、Haiku 等 |
| DeepSeek | deepseek-chat、deepseek-reasoner |
| 通义千问 (Qwen) | qwen-turbo、qwen-plus、qwen-max |
| 文心一言 (ERNIE) | ernie-4.0、ernie-3.5 |
| 混元 (Hunyuan) | hunyuan-standard、hunyuan-pro |
| 豆包 (Doubao) | doubao-pro-32k 等 |
| 火山方舟 (Ark) | 支持 OpenAI 兼容协议 |
| 智谱 AI (GLM) | glm-4、glm-4-flash |
| Kimi (Moonshot) | moonshot-v1-8k、moonshot-v1-32k |
| MiniMax | MiniMax-Text-01 |
| 讯飞星火 (Spark) | spark-max、spark-pro |
| 零一万物 (Yi) | yi-large、yi-medium |
| 阶跃星辰 (StepFun) | step-1、step-2 |
| 百川 (Baichuan) | Baichuan4、Baichuan3-Turbo |

> 所有国产模型均走 OpenAI 兼容通道，模型名称支持下拉选择和手动输入。

---

## 安装方法

1. 下载插件压缩包
2. 进入 WordPress 后台 → 插件 → 安装插件 → 上传插件
3. 上传 `autoseo-pro.zip` 并激活
4. 进入 **AutoSEO Pro → 设置** 配置 AI 服务商和 API 密钥

---

## 使用说明

### 配置 AI 服务商
1. 进入 **AutoSEO Pro → 设置 → AI 配置**
2. 选择默认 AI 服务商
3. 在对应服务商卡片中填入 API 密钥
4. 选择或手动输入模型名称
5. 点击「保存设置」

### 文章 SEO 设置
在文章编辑页面找到 **AutoSEO Pro** 元框，包含 4 个 Tab：

- **SEO 基础** — 聚焦关键词、SEO 标题、元描述、评分和问题清单
- **AI 工具** — AI 标题建议、AI 摘要生成、一键修复
- **社交媒体** — OG 标题、分享预览、社交文案
- **高级** — Canonical URL、noindex、robots、自定义代码、301 重定向

### 社交分享配置
- 在文章编辑页「社交媒体」Tab 填写分享文案，或点「✨ AI 生成文案」自动生成
- 进入设置页填写「默认 OG 分享图片」URL，作为无封面图文章的兜底图片

---

## 文件结构

```
autoseo-pro/
├── autoseo-pro.php                    # 插件主入口
├── uninstall.php                      # 卸载清理
├── README.md                          # 说明文档
├── assets/
│   └── css/
│       ├── admin.css                  # 后台样式
│       └── share-bar.css             # 分享栏样式
├── admin/
│   ├── class-admin.php               # 菜单注册、AJAX 路由
│   ├── class-dashboard.php           # 仪表盘页面
│   ├── class-settings.php            # 设置页面
│   ├── class-meta-box.php            # 文章元框
│   └── class-rank.php                # 排名监控页面
├── includes/
│   ├── class-loader.php              # 启动加载器
│   ├── class-activator.php           # 激活 / 默认值
│   └── services/
│       ├── class-ai-client.php       # 统一 AI 客户端
│       ├── class-title-generator.php # AI 标题生成
│       ├── class-meta-generator.php  # AI 摘要生成
│       ├── class-keyword-extractor.php # AI 关键词提取
│       ├── class-seo-analyzer.php    # SEO 评分分析
│       ├── class-repair-engine.php   # 一键修复引擎
│       ├── class-rank-monitor.php    # 排名监控
│       ├── class-schema.php          # JSON-LD 结构化数据
│       ├── class-sitemap.php         # XML Sitemap
│       └── class-breadcrumb.php      # 面包屑导航
└── public/
    ├── class-frontend.php            # 前端 meta 标签注入
    └── class-share-bar.php           # 社交分享栏
```

---

## 常见问题

**Q：AI 生成内容为空或报错？**  
A：检查 AI 服务商密钥是否正确，模型名称是否填写准确。推理模型（如 DeepSeek R1）不适合结构化输出任务，建议改用普通对话模型（如 deepseek-chat、doubao-pro-32k）。

**Q：分享到微信朋友圈没有卡片预览？**  
A：需满足以下条件：① 文章有特色图像且文件大小 > 100KB；② 在微信内置浏览器打开页面后点右上角「…」→「分享到朋友圈」；③ 域名已完成 ICP 备案（国内服务器必须）。

**Q：插件是否影响网站性能？**  
A：前端仅注入轻量 meta 标签和分享栏 CSS/JS，不加载任何外部资源，对页面性能影响极小。

**Q：分享文案如何使用？**  
A：在文章编辑页「社交媒体」Tab 填写或 AI 生成分享文案后保存，分享到微博/X 时自动作为分享内容，同时作为微信 `og:description` 显示在卡片描述中。

---

## 更新日志

### v2.0.0
- 全新重构，支持 15 家 AI 服务商
- 新增 AI 聚焦关键词提取
- 新增社交分享栏（插件独立注入，不依赖主题）
- 新增默认 OG 图片兜底设置
- 修复字数统计逻辑（中文按汉字计数，英文按单词计数）
- 修复设置项保存丢失问题（`baidu_zhanzhang_key`、`home_desc` 等）
- 优化 AI Prompt，采用 system/user 双角色，JSON 格式输出更稳定
- 管理员页脚显示插件署名

---

## 开源协议

GPL v2 or later  
Copyright © 2026 [SunnyWP](https://sunnywp.com)
