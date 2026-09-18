jQuery(document).ready(function($) {
    let frame;
    let selectedAttachments = [];

    $('#atv-select-videos').on('click', function(e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select Videos to Add to Gallery',
            button: {
                text: 'Add to Gallery'
            },
            library: {
                type: 'video'
            },
            multiple: true
        });

        frame.on('select', function() {
            const selection = frame.state().get('selection');
            const $preview = $('#atv-selected-preview').empty();
            selectedAttachments = [];

            selection.map(function(attachment) {
                attachment = attachment.toJSON();
                selectedAttachments.push(attachment.id);
                
                $preview.append(`
                    <div class="atv-selected-item">
                        <img src="${attachment.icon}" style="width:40px;">
                        <span>${attachment.title}</span>
                    </div>
                `);
            });

            if (selectedAttachments.length > 0) {
                $('.form-actions').show();
            }
        });

        frame.open();
    });

    $('#atv-save-bulk').on('click', function() {
        const catId = $('#atv-target-category').val();
        if (!catId) {
            alert('Please select a category first.');
            return;
        }

        const $btn = $(this);
        const $progress = $('#atv-bulk-progress');
        $btn.prop('disabled', true).text('Creating Posts...');

        $.post(atvAdmin.ajaxUrl, {
            action: 'atv_bulk_add_videos',
            nonce: atvAdmin.nonce,
            category_id: catId,
            attachment_ids: selectedAttachments
        }, async function(response) {
            if (response.success) {
                const posts = response.data.posts;
                $progress.show().text(`Videos added. Capturing thumbnails (0/${posts.length})...`);
                
                for (let i = 0; i < posts.length; i++) {
                    const post = posts[i];
                    $progress.text(`Capturing thumbnail (${i + 1}/${posts.length}): ${post.video_url.split('/').pop()}...`);
                    try {
                        await processSingleCapture(post.video_url, post.post_id);
                    } catch (err) {
                        console.error('Capture failed for', post.video_url, err);
                        $progress.append(` <span style="color:red;">(Failed)</span>`);
                    }
                }

                $progress.text('All done! Redirecting...');
                setTimeout(() => {
                    alert('Videos and thumbnails added successfully!');
                    window.location.href = 'edit.php?post_type=atv_video';
                }, 500);
            } else {
                alert('Error: ' + response.data);
                $btn.prop('disabled', false).text('Save Videos to Category');
            }
        });
    });

    async function processSingleCapture(videoUrl, postId) {
        return new Promise((resolve, reject) => {
            const $video = $('#atv-helper-video')[0];
            const $canvas = $('#atv-helper-canvas')[0];

            // Setup events BEFORE loading
            $video.onloadeddata = function() {
                $video.currentTime = 10;
            };

            $video.onseeked = function() {
                try {
                    $canvas.width = $video.videoWidth;
                    $canvas.height = $video.videoHeight;
                    $canvas.getContext('2d').drawImage($video, 0, 0, $canvas.width, $canvas.height);
                    const dataUrl = $canvas.toDataURL('image/jpeg', 0.8);
                    
                    $.post(atvAdmin.ajaxUrl, {
                        action: 'atv_save_captured_thumb',
                        nonce: atvAdmin.nonce,
                        post_id: postId,
                        image: dataUrl
                    }, function(res) {
                        if (res.success) resolve();
                        else reject(res.data);
                    });
                } catch (e) {
                    reject(e.message);
                }
            };

            $video.onerror = function() {
                reject('Video load error');
            };

            $video.src = videoUrl;
            $video.load();
            
            // Timeout safety
            setTimeout(() => reject('Timeout'), 15000);
        });
    }

    // Capture Frame at 10s (Individual Edit Page)
    $('#atv-capture-frame').on('click', function() {
        const $btn = $(this);
        const videoUrl = $btn.data('video-url');
        const postId = $btn.data('post-id');
        const $status = $('#atv-capture-status');
        const $video = $('#atv-helper-video')[0];
        const $canvas = $('#atv-helper-canvas')[0];

        if (!videoUrl) return;

        $btn.prop('disabled', true);
        $status.text('Loading video...');

        // Setup events BEFORE loading
        $video.onloadeddata = function() {
            $status.text('Seeking to 10s...');
            $video.currentTime = 10;
        };

        $video.onseeked = function() {
            try {
                $status.text('Capturing...');
                $canvas.width = $video.videoWidth;
                $canvas.height = $video.videoHeight;
                $canvas.getContext('2d').drawImage($video, 0, 0, $canvas.width, $canvas.height);
                
                const dataUrl = $canvas.toDataURL('image/jpeg', 0.8);
                
                $status.text('Saving to server...');
                $.post(atvAdmin.ajaxUrl, {
                    action: 'atv_save_captured_thumb',
                    nonce: atvAdmin.nonce,
                    post_id: postId,
                    image: dataUrl
                }, function(response) {
                    if (response.success) {
                        $status.text('Success! Refreshing...');
                        location.reload();
                    } else {
                        $status.text('Error: ' + response.data);
                        $btn.prop('disabled', false);
                    }
                });
            } catch (e) {
                $status.text('Error: ' + e.message);
                $btn.prop('disabled', false);
            }
        };

        $video.onerror = function() {
            $status.text('Error loading video. CORS issue?');
            $btn.prop('disabled', false);
        };

        $video.src = videoUrl;
        $video.load();
    });
});
