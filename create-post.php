<?php
// Create Post Page - Allow all logged-in users
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = htmlspecialchars($_SESSION['user_email']);
$user_role = $_SESSION['user_role'] ?? 'user';

$page_title = "Create Community Post - Yucca Club";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="icon" type="image/png" href="ui/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="ui/css/styles.css">
    <style>
        .content-section {
            background: var(--off-white);
            border: 2px solid var(--lobo-gray);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .section-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .content-preview {
            border: 1px dashed var(--lobo-gray);
            border-radius: 6px;
            padding: 0.5rem;
            margin-top: 0.5rem;
            background: rgba(0,0,0,0.02);
        }
        .image-placeholder {
            width: 100%;
            height: 200px;
            border: 2px dashed var(--yucca-yellow);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(168,170,25,0.05);
            cursor: pointer;
            transition: all 0.3s;
        }
        .image-placeholder:hover {
            border-color: var(--yucca-yellow);
            background: rgba(168,170,25,0.1);
        }
    </style>
</head>
<body style="background: var(--desert-sand); min-height: 100vh; padding: 2rem 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; background: var(--off-white); border-radius: 12px; padding: 2rem;">
        <h1 style="margin-bottom: 0.75rem;">Create Community Post</h1>
        <p style="margin: 0 0 1rem 0; opacity: 0.8;">You can post up to 5 times per month. Submissions are reviewed before publication.</p>
        
        <form id="post-form">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700;">Title *</label>
                <input type="text" id="post-title" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--lobo-gray); border-radius: 6px;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700;">Category</label>
                <select id="post-category" style="width: 100%; padding: 0.75rem; border: 2px solid var(--lobo-gray); border-radius: 6px;">
                    <option value="">Select a category</option>
                    <option value="Food & Drink">Food & Drink</option>
                    <option value="Outdoors">Outdoors</option>
                    <option value="Events">Events</option>
                    <option value="Culture">Culture</option>
                    <option value="News">News</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700;">Featured Image URL</label>
                <input type="url" id="featured-image" placeholder="https://example.com/image.jpg" style="width: 100%; padding: 0.75rem; border: 2px solid var(--lobo-gray); border-radius: 6px;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700;">Introduction/Lead Paragraph *</label>
                <textarea id="post-intro" rows="3" placeholder="A brief introduction or lead paragraph for your post..." style="width: 100%; padding: 0.75rem; border: 2px solid var(--lobo-gray); border-radius: 6px;"></textarea>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 700;">Content Sections</label>
                <button type="button" id="add-paragraph" style="padding: 0.5rem 1rem; background: var(--yucca-yellow); color: white; border: none; border-radius: 6px; cursor: pointer; margin-right: 0.5rem;">
                    <i class="fas fa-paragraph"></i> Add Text
                </button>
                <button type="button" id="add-image" style="padding: 0.5rem 1rem; background: var(--yucca-yellow); color: white; border: none; border-radius: 6px; cursor: pointer; margin-right: 0.5rem;">
                    <i class="fas fa-image"></i> Add Image
                </button>
                <button type="button" id="add-heading" style="padding: 0.5rem 1rem; background: var(--yucca-yellow); color: white; border: none; border-radius: 6px; cursor: pointer; margin-right: 0.5rem;">
                    <i class="fas fa-heading"></i> Add Heading
                </button>
                <button type="button" id="add-list" style="padding: 0.5rem 1rem; background: var(--yucca-yellow); color: white; border: none; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-list"></i> Add List
                </button>
            </div>
            
            <div id="content-sections" style="margin-bottom: 2rem;">
                <!-- Sections will be added here -->
            </div>
            
            <button type="submit" class="cta-button" style="width: 100%; font-size: 18px; padding: 1rem;">Submit Post for Review</button>
            <a href="nav/community/index.php" style="display: block; text-align: center; margin-top: 1rem; color: var(--lobo-gray);">Cancel</a>
        </form>
    </div>
    
    <script>
        let sectionCounter = 0;
        const contentSections = document.getElementById('content-sections');
        
        function createSection(type, data = {}) {
            const section = document.createElement('div');
            section.className = 'content-section';
            section.dataset.type = type;
            section.dataset.index = sectionCounter++;
            
            let html = '';
            switch(type) {
                case 'paragraph':
                    html = `
                        <strong><i class="fas fa-paragraph"></i> Text Paragraph</strong>
                        <textarea class="section-content" rows="4" placeholder="Write your paragraph here...">${data.text || ''}</textarea>
                    `;
                    break;
                case 'image':
                    html = `
                        <strong><i class="fas fa-image"></i> Image</strong>
                        <input type="url" class="section-image-url" placeholder="Image URL" value="${data.url || ''}" style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        <input type="text" class="section-image-alt" placeholder="Alt text (optional)" value="${data.alt || ''}" style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        <div class="content-preview">
                            ${data.url ? `<img src="${data.url}" alt="${data.alt || ''}" style="max-width: 100%; height: auto; border-radius: 4px;">` : '<div class="image-placeholder"><i class="fas fa-image" style="font-size: 3rem; opacity: 0.3;"></i></div>'}
                        </div>
                    `;
                    // Update preview on URL change
                    section.querySelector('.section-image-url')?.addEventListener('input', function(e) {
                        const img = section.querySelector('.content-preview img');
                        const placeholder = section.querySelector('.image-placeholder');
                        if (e.target.value) {
                            if (img) {
                                img.src = e.target.value;
                            } else if (placeholder) {
                                placeholder.outerHTML = `<img src="${e.target.value}" alt="" style="max-width: 100%; height: auto; border-radius: 4px;">`;
                            }
                        }
                    });
                    break;
                case 'heading':
                    html = `
                        <strong><i class="fas fa-heading"></i> Heading</strong>
                        <input type="text" class="section-content" placeholder="Enter heading text..." value="${data.text || ''}" style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    `;
                    break;
                case 'list':
                    html = `
                        <strong><i class="fas fa-list"></i> List</strong>
                        <textarea class="section-content" rows="4" placeholder="Enter list items, one per line...">${data.items || ''}</textarea>
                    `;
                    break;
            }
            
            html += `
                <div class="section-actions">
                    <button type="button" class="move-up" style="padding: 0.25rem 0.5rem; background: #ddd; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-arrow-up"></i> Up
                    </button>
                    <button type="button" class="move-down" style="padding: 0.25rem 0.5rem; background: #ddd; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-arrow-down"></i> Down
                    </button>
                    <button type="button" class="delete-section" style="padding: 0.25rem 0.5rem; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            `;
            
            section.innerHTML = html;
            return section;
        }
        
        document.getElementById('add-paragraph').addEventListener('click', () => {
            contentSections.appendChild(createSection('paragraph'));
        });
        
        document.getElementById('add-image').addEventListener('click', () => {
            contentSections.appendChild(createSection('image'));
        });
        
        document.getElementById('add-heading').addEventListener('click', () => {
            contentSections.appendChild(createSection('heading'));
        });
        
        document.getElementById('add-list').addEventListener('click', () => {
            contentSections.appendChild(createSection('list'));
        });
        
        contentSections.addEventListener('click', (e) => {
            if (e.target.closest('.delete-section')) {
                e.target.closest('.content-section').remove();
            }
            
            if (e.target.closest('.move-up')) {
                const section = e.target.closest('.content-section');
                const prev = section.previousElementSibling;
                if (prev) {
                    section.parentNode.insertBefore(section, prev);
                }
            }
            
            if (e.target.closest('.move-down')) {
                const section = e.target.closest('.content-section');
                const next = section.nextElementSibling;
                if (next) {
                    section.parentNode.insertBefore(next, section);
                }
            }
        });
        
        document.getElementById('post-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const title = document.getElementById('post-title').value;
            const category = document.getElementById('post-category').value;
            const featuredImage = document.getElementById('featured-image').value;
            const intro = document.getElementById('post-intro').value;
            
            // Build content array from sections
            const sections = document.querySelectorAll('.content-section');
            const contentArray = [];
            
            sections.forEach(section => {
                const type = section.dataset.type;
                const data = {};
                
                if (type === 'paragraph' || type === 'list') {
                    data.text = section.querySelector('.section-content').value;
                } else if (type === 'heading') {
                    data.text = section.querySelector('.section-content').value;
                } else if (type === 'image') {
                    data.url = section.querySelector('.section-image-url').value;
                    data.alt = section.querySelector('.section-image-alt').value;
                }
                
                contentArray.push({ type, data });
            });
            
            // Build final content JSON
            const finalContent = {
                intro: intro,
                sections: contentArray
            };
            
            try {
                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('title', title);
                formData.append('content', JSON.stringify(finalContent));
                formData.append('category', category);
                formData.append('featured_image', featuredImage);
                
                const response = await fetch('api/user_posts_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Post submitted successfully! It will be reviewed before publication.');
                    window.location.href = 'nav/community/index.php';
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error submitting post: ' + error.message);
            }
        });
    </script>
</body>
</html>
