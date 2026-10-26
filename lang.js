const translations = {
    pt: {
        nav_home: "Início",
        nav_catalog: "Catálogo",
        nav_quote: "Solicitar Cotação",

        banner_title: "Excelência e Compromisso Offshore",

        cat1: "Elétrica VDE",
        cat2: "Iluminação LED",
        cat3: "Ferramentas",
        cat4: "Hidráulica",
        cat5: "EPIs & Vedações",

        portfolio_title: "Destaques do Portfólio 2026",
        portfolio_desc: "Marcas líderes e equipamentos com certificação de qualidade.",

        prod_desc:"Trabalhamos com",

        prod1_tag: "FLUXO E PRESSÃO",
        prod1_title: "Bombas d'Água",
        prod1_desc: "Soluções robustas para transferência de fluidos.",
        prod1_sku: "ALTA CONFIABILIDADE",

        prod2_tag: "SEGURANÇA VDE",
        prod2_title: "Linha Elétrica",
        prod2_desc: "Instalações elétricas seguras.",
        prod2_sku: "LINHA PROFISSIONAL",

        prod3_tag: "PROTEÇÃO INDIVIDUAL",
        prod3_title: "EPIs e Vedações",
        prod3_desc: "Segurança operacional.",
        prod3_sku: "NORMA OFFSHORE",

        about_tag: "NOSSA ESSÊNCIA",
        about_title: "Compromisso com Resultado e Confiabilidade",
        about_p1: "A Fly Marine Offshore foi criada para resolver desafios reais da operação offshore.",
        about_p2: "Garantimos segurança operacional e previsibilidade.",

        hero_title: "Soluções que Agregam Valor à sua Operação",
        hero_desc: "Clareza, ética e prazos.",

        catalog_title: "Catálogo Completo",
        catalog_desc: "Acesse as especificações técnicas.",
        download_btn: "BAIXAR PDF",

        contact_title: "Atendimento Técnico",
        contact_desc: "Equipe pronta para ajudar.",
        email_btn: "ENVIAR E-MAIL",
        partners_title: "Parceiros",
        partners_desc: "Trabalhamos com marcas reconhecidas e seguimos padrões rigorosos de qualidade.",
        partners_subtitle: "Parceiros",
        certifications_subtitle: "Certificação",

        cert_iso: "ISO 9001",
        cert_inmetro: "INMETRO",
        cert_nr: "Normas NR",

        mvv_title: "Missão, Visão e Valores",
        mvv_missao_title: "Missão",
        mvv_missao_desc: "Fornecer soluções offshore confiáveis e certificadas que agregam segurança e eficiência operacional.",
        mvv_visao_title: "Visão",
        mvv_visao_desc: "Ser referência em qualidade e confiabilidade na indústria offshore, reconhecida por excelência certificada.",
        mvv_valores_title: "Valores",
        mvv_valores_desc: "Qualidade certificada, ética, compromisso e confiabilidade em cada operação e relacionamento."
    },

    en: {
        nav_home: "Home",
        nav_catalog: "Catalog",
        nav_quote: "Request Quote",

        banner_title: "Excellence and Offshore Commitment",

        cat1: "Electrical VDE",
        cat2: "LED Lighting",
        cat3: "Tools",
        cat4: "Hydraulics",
        cat5: "PPE & Sealing",

        portfolio_title: "2026 Portfolio Highlights",
        portfolio_desc: "Leading brands and certified equipment.",

        prod_desc:"We work with",
        prod1_tag: "FLOW & PRESSURE",
        prod1_title: "Water Pumps",
        prod1_desc: "Robust solutions for fluid transfer.",
        prod1_sku: "HIGH RELIABILITY",

        prod2_tag: "VDE SAFETY",
        prod2_title: "Electrical Line",
        prod2_desc: "Safe electrical installations.",
        prod2_sku: "PROFESSIONAL LINE",

        prod3_tag: "PERSONAL PROTECTION",
        prod3_title: "PPE & Sealing",
        prod3_desc: "Operational safety.",
        prod3_sku: "OFFSHORE STANDARD",

        about_tag: "OUR ESSENCE",
        about_title: "Commitment to Results and Reliability",
        about_p1: "Fly Marine Offshore was created to solve real offshore challenges.",
        about_p2: "We ensure safety and predictability.",

        hero_title: "Solutions That Add Value to Your Operation",
        hero_desc: "Clarity, ethics, and deadlines.",

        catalog_title: "Full Catalog",
        catalog_desc: "Access technical specifications.",
        download_btn: "DOWNLOAD PDF",

        contact_title: "Technical Support",
        contact_desc: "Team ready to help.",
        email_btn: "SEND EMAIL",

        partners_title: "Partners",
        partners_desc: "We work with recognized brands and follow strict quality standards.",
        partners_subtitle: "Partners",
        certifications_subtitle: "Certification",

        cert_iso: "ISO 9001",
        cert_inmetro: "INMETRO",
        cert_nr: "NR Standards",

        mvv_title: "Mission, Vision and Values",
        mvv_missao_title: "Mission",
        mvv_missao_desc: "Provide reliable and certified offshore solutions that add safety and operational efficiency.",
        mvv_visao_title: "Vision",
        mvv_visao_desc: "Be a reference in quality and reliability in the offshore industry, recognized for certified excellence.",
        mvv_valores_title: "Values",
        mvv_valores_desc: "Certified quality, ethics, commitment and reliability in every operation and relationship."
    }
};

function switchLang(lang) {
    document.querySelectorAll("[data-i18n]").forEach(el => {
        const key = el.getAttribute("data-i18n");
        if (translations[lang][key]) {
            el.textContent = translations[lang][key];
        }
    });

    document.documentElement.lang = lang;

    if (typeof window.updateProductLanguage === 'function') {
        window.updateProductLanguage(lang);
    }
}

document.querySelectorAll(".lang-switch button").forEach(btn => {
    btn.addEventListener("click", () => {
        const lang = btn.getAttribute("data-lang");
        switchLang(lang);
    });
});

switchLang("pt");