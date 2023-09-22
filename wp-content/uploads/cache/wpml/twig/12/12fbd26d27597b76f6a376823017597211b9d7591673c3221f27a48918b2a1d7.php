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

/* /setup/display-as-translated.twig */
class __TwigTemplate_5aac3a376625dfaed0c33578fd81c14bb46bac0181685e0e11896ea2cc22f30d extends \WPML\Core\Twig\Template
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

<ul class=\"no-bullets\">
    <li>
        <label>
            <input type=\"radio\" value=\"yes\" name=\"display-as-translated\" required ";
        // line 7
        if (($context["is_display_as_translated_checked"] ?? null)) {
            echo "checked=\"checked\"";
        }
        echo " />
            ";
        // line 8
        echo $this->getAttribute(($context["strings"] ?? null), "label_display_as_translated", []);
        echo "
        </label>
    </li>
    <li>
        <label>
            <input type=\"radio\" value=\"no\" name=\"display-as-translated\" required />
            ";
        // line 14
        echo $this->getAttribute(($context["strings"] ?? null), "label_dont_display_as_translated", []);
        echo "
        </label>
    </li>
</ul>

<p>";
        // line 19
        echo sprintf($this->getAttribute(($context["strings"] ?? null), "description_footer", []), "<strong>", "</strong>");
        echo "</p>

<p class=\"wcml-setup-actions step\">
    <a href=\"";
        // line 22
        echo \WPML\Core\twig_escape_filter($this->env, ($context["go_back_url"] ?? null), "html", null, true);
        echo "\" class=\"go-back\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "go_back", []), "html", null, true);
        echo "</a>
    <a href=\"";
        // line 23
        echo \WPML\Core\twig_escape_filter($this->env, ($context["continue_url"] ?? null), "html", null, true);
        echo "\" class=\"button button-large button-primary submit\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "continue", []), "html", null, true);
        echo "</a>
</p>
</span>";
    }

    public function getTemplateName()
    {
        return "/setup/display-as-translated.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  80 => 23,  74 => 22,  68 => 19,  60 => 14,  51 => 8,  45 => 7,  37 => 2,  32 => 1,);
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

<ul class=\"no-bullets\">
    <li>
        <label>
            <input type=\"radio\" value=\"yes\" name=\"display-as-translated\" required {% if is_display_as_translated_checked %}checked=\"checked\"{% endif %} />
            {{ strings.label_display_as_translated|raw }}
        </label>
    </li>
    <li>
        <label>
            <input type=\"radio\" value=\"no\" name=\"display-as-translated\" required />
            {{ strings.label_dont_display_as_translated|raw }}
        </label>
    </li>
</ul>

<p>{{ strings.description_footer|format('<strong>','</strong>')|raw }}</p>

<p class=\"wcml-setup-actions step\">
    <a href=\"{{ go_back_url }}\" class=\"go-back\">{{ strings.go_back }}</a>
    <a href=\"{{ continue_url }}\" class=\"button button-large button-primary submit\">{{ strings.continue }}</a>
</p>
</span>", "/setup/display-as-translated.twig", "/var/www/bike.meihao.shopping/htdocs/wp-content/plugins/woocommerce-multilingual/templates/setup/display-as-translated.twig");
    }
}
