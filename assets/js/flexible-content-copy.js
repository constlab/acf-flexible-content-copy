(function ($, _, Backbone) {

    'use strict';

    ///// Models and Collection

    var Post = Backbone.Model.extend();

    var Layout = Backbone.Model.extend({
        url: FlexibleContentCopyLocalize.url
    });

    var SearchResults = Backbone.Collection.extend({
        model: Post,
        url: FlexibleContentCopyLocalize.url,
        parse: function (response) {
            if (response.length == 0) {
                this.trigger('reset');
            }
            return response;
        }
    });

    ///// Events

    var Events = {};
    _.extend(Events, Backbone.Events);

    ///// Views

    /**
     * Dialog View
     */
    var DialogView = Backbone.View.extend({
        className: 'flexible-content-copy-dialog', // for css
        template: _.template($('#flexible-content-copy-template').html()),
        initialize: function (opts) {
            this.field = opts.field;
            if (this.field === undefined) {
                console.error('No field id. Saving impossible');
            }
            this.collection = new SearchResults();
            this.listenTo(this.collection, 'reset', this.renderSearchResult, this);
            Events.bind('detail', this.toggleDetail, this);
            this.render();
        },
        render: function () {
            this.$el.html(this.template());
            return this;
        },
        changeSearchStatus: function (searching) {
            if (searching) {
                this.$el.find('.status-text').removeClass('empty');
                this.$el.find('.status-text').html('<img src="' + FlexibleContentCopyLocalize.loader + '">');
                this.$el.find('input[type="search"]').prop('disabled', true);
            } else {
                this.$el.find('.status-text').addClass('empty');
                this.$el.find('.status-text').html('');
                this.$el.find('input[type="search"]').prop('disabled', false);
            }
        },
        renderSearchResult: function () {
            this.cleanSearchResult();
            this.collection.each(this.renderResultRow, this);
        },
        renderResultRow: function (post) {
            var rowView = new RowView({model: post});
            this.$el.find('.search-result tbody').append(rowView.render().el);
        },
        cleanSearchResult: function () {
            this.$el.find('.search-result .result-row').remove();
        },
        toggleDetail: function (row) {
            this.removeDetails();
            var model = new Layout({
                id: row.model.get('id'),
                title: row.model.get('title'),
                formUrl: FlexibleContentCopyLocalize.url + '?action=flexible-content-copy/save',
                field: this.field
            });
            var detailView = new DetailView({model: model});

            this.$el.append(detailView.render().el);
            _.defer(function () {
                detailView.show()
            });
        },
        removeDetails: function () {
            this.$el.find('.post-detail-view').remove();
        },
        events: {
            'change input[type="search"]': 'onSearch'
        },
        onSearch: function () {
            this.changeSearchStatus(true);
            this.collection.reset();
            this.removeDetails();
            var vm = this;
            this.collection.fetch({
                reset: true,
                data: {
                    action: 'flexible-content-copy/load-posts',
                    q: this.$el.find('input[type="search"]').val()
                },
                success: function () {
                    vm.changeSearchStatus(false);
                }
            });
        }
    });

    /**
     * Table Row View
     */
    var RowView = Backbone.View.extend({
        tagName: 'tr',
        className: 'result-row',
        template: _.template($('#flexible-content-copy-row-template').html()),
        render: function () {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        },
        events: {
            'click .post-title': 'onRowClick'
        },
        onRowClick: function () {
            Events.trigger('detail', this);
        }
    });

    /**
     * Post Detail View
     */
    var DetailView = Backbone.View.extend({
        className: 'post-detail-view',
        template: _.template($('#flexible-content-copy-detail-template').html()),
        initialize: function () {
            this.height = $('#TB_ajaxContent').innerHeight() - 10;
            this.listenTo(this.model, 'change', this.renderLayouts, this);
            this.model.fetch({
                data: {
                    action: 'flexible-content-copy/layouts',
                    post: this.model.get('id')
                }
            });
        },
        render: function () {
            var model = this.model.toJSON();
            model.height = this.height;
            this.$el.html(this.template(model));
            this.$el.find('#layouts-list').html('<img src="' + FlexibleContentCopyLocalize.loader + '">');
            return this;
        },
        renderLayouts: function () {
            this.$el.find('#layouts-list').empty();
            var layoutsHtml = '';
            var layouts = this.model.get('layouts');
            if (layouts.length) {
                _.each(layouts, function (el) {
                    layoutsHtml += _.template('<li><label><input type="checkbox" name="flexible[<%= name %>-<%= order %>]">' +
                        '&nbsp;<%= label %></label></li>', el);
                });
                this.$el.find('#layouts-list').html(layoutsHtml);
            } else {
                this.$el.find('#layouts-list').html('<li>No layouts</li>');
            }
        },
        show: function () {
            this.$el.animate({'margin-right': '0'}, 400);
        },
        events: {
            'click .close': 'onClose',
            'click .button-primary': 'onSubmit'
        },
        onClose: function () {
            this.$el.animate({'margin-right': '-=5000'}, 500);
            return false;
        },
        onSubmit: function () {
            this.$el.find('form').submit();
            return false;
        }
    });

    /**
     * App View
     */
    var AppView = Backbone.View.extend({
        initialize: function (options) {
            this.fieldArray = options.field.split('_');
            if (this.fieldArray.length == 2) {
                this.field = this.fieldArray[1];
            } else {
                return;
            }
            delete this.fieldArray;
            delete options.field;
            this.template = _.template($('#flexible-content-copy').html());
            this.render();
        },
        render: function () {
            this.$el.append(this.template());
            return this;
        },
        events: {
            'click .open-dialog': 'onOpenDialog'
        },
        onOpenDialog: function () {
            this.dialogView = new DialogView({field: this.field});
            var $dialogEl = this.dialogView.render().$el;
            $('#flexible-content-copy-dialog').html($dialogEl);
            tb_show('Flexible Content Copy', '#TB_inline?inlineId=flexible-content-copy-dialog', false);
            $dialogEl.find('input[type="search"]').focus();
            calcDialogHeight();
            return false;
        }
    });

    /////////

    function calcDialogHeight() {
        var windowHeight = $(window).height();
        var $dialog = $('#TB_ajaxContent');
        var maxHeight = Math.round((windowHeight / 100) * 85);
        var minHeight = Math.round((windowHeight / 100) * 50);
        $dialog.css('max-height', maxHeight + 'px');
        $dialog.css('min-height', minHeight + 'px');
    }

    $(window).resize(function () {
        calcDialogHeight();
    });

    $(window).load(function () {
        var field = $('.acf-field-flexible-content').attr('data-key');
        if (field === undefined) {
            return;
        }
        if (_.isArray(field)) {
            console.log('not supported yet');
        } else {
            new AppView({
                el: '.acf-field-flexible-content[data-key="' + field + '"] .values ~ ul.acf-hl',
                field: field
            });
        }
    });

})(jQuery, _, Backbone);