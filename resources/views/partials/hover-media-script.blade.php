<script>
document.querySelectorAll('.js-hover-preview').forEach((media) => {
    const resetVideo = () => {
        media.pause();
        media.currentTime = 0;
    };

    media.addEventListener('mouseenter', () => {
        media.play().catch(() => {});
    });

    media.addEventListener('mouseleave', resetVideo);
    media.addEventListener('blur', resetVideo);
});
</script>
