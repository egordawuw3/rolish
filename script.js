document.addEventListener("DOMContentLoaded", () => {
    gsap.registerPlugin(ScrollTrigger, Draggable, InertiaPlugin);

    // --- HERO WORD ROTATOR ---
    const words = ["Креативность", "Съемка", "Монтаж", "Продажи"];
    const rotatorWrapper = document.querySelector(".rotator-wrapper");
    if (rotatorWrapper) {
        words.forEach(word => {
            const div = document.createElement('div');
            div.className = 'rotator-word';
            div.textContent = word;
            rotatorWrapper.appendChild(div);
        });

        let currentWord = 0;
        const wordElements = gsap.utils.toArray(".rotator-word");
        if(wordElements.length > 0) {
            const wordHeight = wordElements[0].clientHeight;
            gsap.set(rotatorWrapper, { y: 0 });

            function rotateWords() {
                currentWord = (currentWord + 1) % words.length;
                gsap.to(rotatorWrapper, {
                    y: -currentWord * wordHeight,
                    duration: 0.7,
                    ease: "power3.inOut"
                });
            }
            setInterval(rotateWords, 2500);
        }
    }

    // --- DRAGGABLE ASSETS ---
    const assets = ["#camera", "#clapperboard", "#laptop", "#microsd"];
    assets.forEach(assetId => {
        Draggable.create(assetId, {
            type: "x,y",
            edgeResistance: 0.75,
            bounds: "body",
            inertia: {
                resistance: 25,
                minSpeed: 100,
                maxSpeed: 1200
            },
            onDrag: function() {
                gsap.to(this.target, { duration: 0.2, scale: 1.05 });
            },
            onDragEnd: function() {
                 gsap.to(this.target, { duration: 0.5, scale: 1, ease: "power2.out" });
                // Spring-back animation
                 gsap.to(this.target, { 
                    x: this.vars.snap.x, 
                    y: this.vars.snap.y, 
                    duration: 1.2, 
                    ease: "elastic.out(1, 0.4)" 
                });
            },
             // Store initial position for snap
            snap: {
                x: function(endValue) {
                    return gsap.getProperty(this.target, "x", "px");
                },
                y: function(endValue) {
                    return gsap.getProperty(this.target, "y", "px");
                }
            }
        });
        // Set initial snap points after a delay to ensure correct layout
        setTimeout(() => {
            const el = document.querySelector(assetId);
            if(el) {
                Draggable.get(el).vars.snap.x = el._gsap.x;
                Draggable.get(el).vars.snap.y = el._gsap.y;
            }
        }, 100);
    });

    // --- VSL FOLDER 3D OPEN ---
    const folder = document.getElementById("vsl-folder");
    if (folder) {
        const lid = folder.querySelector(".folder-lid");
        gsap.set(lid, { transformStyle: "preserve-3d", rotationX: 0 });

        const openTl = gsap.timeline({ paused: true, reversed: true })
            .to(lid, {
                rotationX: -170,
                duration: 1.2,
                ease: "power4.inOut"
            });
            
        ScrollTrigger.create({
            trigger: folder,
            start: "top 70%",
            end: "bottom top",
            onEnter: () => openTl.play(),
            onLeaveBack: () => openTl.reverse(),
        });
    }

    // --- STORY HORIZONTAL SCROLL ---
    const storyWrapper = document.querySelector(".story-track-wrapper");
    if(storyWrapper) {
        const storyTrack = document.querySelector(".story-track");
        const horizontalScroll = gsap.to(storyTrack, {
            x: () => -(storyTrack.scrollWidth - storyWrapper.offsetWidth),
            ease: "none",
            scrollTrigger: {
                trigger: storyWrapper,
                start: "top top",
                end: () => `+=${storyTrack.scrollWidth - storyWrapper.offsetWidth}`,
                scrub: 1.5,
                pin: true,
                invalidateOnRefresh: true,
                anticipatePin: 1
            }
        });
    }

    // --- CASES 3D FLIP ON HOVER ---
    document.querySelectorAll('.polaroid-card').forEach(card => {
        const flipper = card.querySelector('.card-flipper');
        gsap.set(flipper, { rotationY: 0 });

        const flipAnimation = gsap.to(flipper, {
            rotationY: 180,
            duration: 0.8,
            ease: 'power3.inOut',
            paused: true
        });

        card.addEventListener('mouseenter', () => flipAnimation.play());
        card.addEventListener('mouseleave', () => flipAnimation.reverse());
    });
    
    // --- FADE-IN ANIMATIONS ON SCROLL ---
    const revealElements = ['.section-title', '.vsl-text-content > *', '.cases-grid', '.modules-grid', '.bonus-card'];
    revealElements.forEach(selector => {
        gsap.utils.toArray(selector).forEach(el => {
            gsap.from(el, {
                scrollTrigger: {
                    trigger: el,
                    start: 'top 90%',
                    toggleActions: 'play none none none'
                },
                opacity: 0,
                y: 60,
                duration: 1.2,
                ease: 'power3.out',
                stagger: 0.1
            });
        });
    });
});
