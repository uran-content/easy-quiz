document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;

    const setCursorPosition = (x, y) => {
        const percentX = (x / window.innerWidth) * 100;
        const percentY = (y / window.innerHeight) * 100;
        body.style.setProperty('--cursor-x', `${percentX}%`);
        body.style.setProperty('--cursor-y', `${percentY}%`);
    };

    const handlePointer = (event) => {
        if (event.touches && event.touches.length > 0) {
            const touch = event.touches[0];
            setCursorPosition(touch.clientX, touch.clientY);
        } else {
            setCursorPosition(event.clientX, event.clientY);
        }
    };

    window.addEventListener('mousemove', handlePointer, { passive: true });
    window.addEventListener('touchmove', handlePointer, { passive: true });

    setCursorPosition(window.innerWidth / 2, window.innerHeight / 2);

    const hero = document.querySelector('.hero-visual');
    if (hero) {
        hero.addEventListener('pointermove', (event) => {
            const rect = hero.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width - 0.5) * 16;
            const y = ((event.clientY - rect.top) / rect.height - 0.5) * 16;
            hero.style.setProperty('--tilt-x', `${y}deg`);
            hero.style.setProperty('--tilt-y', `${-x}deg`);
        });

        hero.addEventListener('pointerleave', () => {
            hero.style.removeProperty('--tilt-x');
            hero.style.removeProperty('--tilt-y');
        });
    }
});
