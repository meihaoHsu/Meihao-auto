<?php

namespace WPML\Core;

use \WPML\Core\Twig\Environment;
use \WPML\Core\Twig\Error\LoaderError;
use \WPML\Core\Twig\Error\RuntimeError;
use \WPML\Core\Twig\Markup;
use \WPML\Core\Twig\Sandbox\SecurityError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedTagError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFilterError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFunctionError;
use \WPML\Core\Twig\Source;
use \WPML\Core\Twig\Template;

/* /setup/translation-options.twig */
class __TwigTemplate_53fd3df41bbf9fb35e028ae99de6ceb0e51a37c912c96fffba6b39597a8ad292 extends \WPML\Core\Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<span id=\"";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "step_id", []), "html", null, true);
        echo "\">
<h1>";
        // line 2
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "heading", []), "html", null, true);
        echo "</h1>

<div class=\"translation-options\">
    <ul class=\"no-bullets\">
        <li>
            <label class=\"wcml-translate-everything\">
                <span class=\"wpml-translate-choose\">";
        // line 8
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "label_choose", []), "html", null, true);
        echo "</span>
                <input type=\"radio\" value=\"translate_everything\" name=\"translation-option\" required checked=\"checked\" />
                <div class=\"wcml-icon\"><i class=\"otgs-ico otgs-ico-lightning-o\"></i></div>
                <h3>";
        // line 11
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "label_translate_everything", []), "html", null, true);
        echo "</h3>
                <p>";
        // line 12
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "description_translate_everything", []), "html", null, true);
        echo "</p>
            </label>
        </li>
        <li>
            <label class=\"wcml-translate-some\">
                <span class=\"wpml-translate-choose\">";
        // line 17
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "label_choose", []), "html", null, true);
        echo "</span>
                <input type=\"radio\" value=\"translate_some\" name=\"translation-option\" required />
                <div class=\"wcml-icon\"><i class=\"otgs-ico otgs-ico-wpml-translation-management\"></i></div>
                <h3>";
        // line 20
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "label_translate_some", []), "html", null, true);
        echo "</h3>
                <strong>";
        // line 21
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "label_translate_some", []), "html", null, true);
        echo "</strong>
                <p>";
        // line 22
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "description_translate_some", []), "html", null, true);
        echo "</p>
            </label>
        </li>
    </ul>
</div>

<p class=\"text-center\">";
        // line 28
        echo sprintf($this->getAttribute(($context["strings"] ?? null), "description_footer", []), "<strong>", "</strong>");
        echo "</p>

<p class=\"wcml-setup-actions step\">
    <a href=\"";
        // line 31
        echo \WPML\Core\twig_escape_filter($this->env, ($context["go_back_url"] ?? null), "html", null, true);
        echo "\" class=\"go-back\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "go_back", []), "html", null, true);
        echo "</a>
    <a href=\"";
        // line 32
        echo \WPML\Core\twig_escape_filter($this->env, ($context["continue_url"] ?? null), "html", null, true);
        echo "\" class=\"button button-large button-primary submit\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "continue", []), "html", null, true);
        echo "</a>
</p>
</span>
";
    }

    public function getTemplateName()
    {
        return "/setup/translation-options.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  99 => 32,  93 => 31,  87 => 28,  78 => 22,  74 => 21,  70 => 20,  64 => 17,  56 => 12,  52 => 11,  46 => 8,  37 => 2,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("<span id=\"{{ strings.step_id }}\">
<h1>{{ strings.heading }}</h1>

<div class=\"translation-options\">
    <ul class=\"no-bullets\">
        <li>
            <label class=\"wcml-translate-everything\">
                <span class=\"wpml-translate-choose\">{{ strings.label_choose }}</span>
                <input type=\"radio\" value=\"translate_everything\" name=\"translation-option\" required checked=\"checked\" />
                <div class=\"wcml-icon\"><i class=\"otgs-ico otgs-ico-lightning-o\"></i></div>
                <h3>{{ strings.label_translate_everything }}</h3>
                <p>{{ strings.description_translate_everything }}</p>
            </label>
        </li>
        <li>
            <label class=\"wcml-translate-some\">
                <span class=\"wpml-translate-choose\">{{ strings.label_choose }}</span>
                <input type=\"radio\" value=\"translate_some\" name=\"translation-option\" required />
                <div class=\"wcml-icon\"><i class=\"otgs-ico otgs-ico-wpml-translation-management\"></i></div>
                <h3>{{ strings.label_translate_some }}</h3>
                <strong>{{ strings.label_translate_some }}</strong>
                <p>{{ strings.description_translate_some }}</p>
            </label>
        </li>
    </ul>
</div>

<p class=\"text-center\">{{ strings.description_footer|format('<strong>','</strong>')|raw }}</p>

<p class=\"wcml-setup-actions step\">
    <a href=\"{{ go_back_url }}\" class=\"go-back\">{{ strings.go_back }}</a>
    <a href=\"{{ continue_url }}\" class=\"button button-large button-primary submit\">{{ strings.continue }}</a>
</p>
</span>
", "/setup/translation-options.twig", "/var/www/bike.meihao.shopping/htdocs/wp-content/plugins/woocommerce-multilingual/templates/setup/translation-options.twig");
    }
}
