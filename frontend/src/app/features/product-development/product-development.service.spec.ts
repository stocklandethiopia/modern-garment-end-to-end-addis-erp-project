import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { PatternRevisionInput, ProductDevelopmentService } from './product-development.service';

describe('ProductDevelopmentService', () => {
  let service: ProductDevelopmentService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(ProductDevelopmentService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('lists product briefs and retrieves immutable brief versions and decisions', () => {
    service.listProductBriefs().subscribe((response) => {
      expect(response.data.data[0]?.status).toBe('ACCEPTED');
      expect(response.data.data[0]?.accepted_version).toBe(1);
    });
    const list = http.expectOne('/api/v1/product-briefs');
    expect(list.request.method).toBe('GET');
    list.flush({ data: { data: [{ id: 'brief-1', status: 'ACCEPTED', accepted_version: 1 }] } });

    service.getProductBrief('brief-1').subscribe((response) => {
      expect(response.versions[0]?.version_no).toBe(1);
      expect(response.decisions[0]?.decision).toBe('ACCEPT');
    });
    const detail = http.expectOne('/api/v1/product-briefs/brief-1');
    expect(detail.request.method).toBe('GET');
    detail.flush({
      data: { id: 'brief-1', status: 'ACCEPTED' },
      versions: [{ id: 'brief-version-1', version_no: 1 }],
      decisions: [{ id: 'decision-1', decision: 'ACCEPT' }],
      styles: [],
    });
  });

  it('submits and independently accepts a product brief through command endpoints', () => {
    service.submitProductBrief('brief-1').subscribe((response) => {
      expect(response.data.status).toBe('SUBMITTED_FOR_REVIEW');
    });
    const submit = http.expectOne('/api/v1/product-briefs/brief-1/submit');
    expect(submit.request.method).toBe('POST');
    submit.flush({ data: { id: 'brief-1', status: 'SUBMITTED_FOR_REVIEW' } });

    service.decideProductBrief('brief-1', {
      decision: 'ACCEPT',
      reason: 'The design team can now proceed from this brief.',
      actions: [],
    }).subscribe((response) => expect(response.data.status).toBe('ACCEPTED'));
    const decision = http.expectOne('/api/v1/product-briefs/brief-1/decisions');
    expect(decision.request.body.decision).toBe('ACCEPT');
    decision.flush({ data: { id: 'brief-1', status: 'ACCEPTED' } });
  });

  it('creates a style and appends a new immutable design revision', () => {
    service.createStyle({
      product_brief_id: 'brief-1',
      style_code: 'ST-001',
      name: 'Everyday overshirt',
      category_code: 'CASUALWEAR',
      design_direction: 'An everyday overshirt with a relaxed silhouette.',
      silhouette: 'Relaxed',
      fit_intent: 'Comfortable movement through shoulder and chest.',
      construction_intent: 'Durable seams and reinforced stress points.',
      material_direction: { fibre: 'Cotton blend' },
      colour_direction: { core: ['NAVY'] },
      target_size_range: { sizes: ['S', 'M', 'L'] },
      change_summary: 'Initial approved-brief style direction.',
    }).subscribe((response) => {
      expect(response.data.product_brief_version).toBe(1);
      expect(response.revision.revision_no).toBe(1);
    });
    const create = http.expectOne('/api/v1/styles');
    expect(create.request.method).toBe('POST');
    create.flush({ data: { id: 'style-1', product_brief_version: 1 }, revision: { id: 'rev-1', revision_no: 1 } });

    service.createStyleRevision('style-1', {
      design_direction: 'Refined overshirt with reduced seam bulk.',
      silhouette: 'Relaxed straight',
      fit_intent: 'Improve sleeve reach without restricting movement.',
      construction_intent: 'Reinforce pocket corners and stress points.',
      material_direction: { fibre: 'Cotton blend', weight: 'Midweight' },
      colour_direction: { core: ['NAVY', 'OLIVE'] },
      target_size_range: { sizes: ['S', 'M', 'L', 'XL'] },
      change_summary: 'Refine sleeve and shoulder balance.',
    }).subscribe((response) => expect(response.data.current_revision).toBe(2));
    const revision = http.expectOne('/api/v1/styles/style-1/revisions');
    expect(revision.request.method).toBe('POST');
    revision.flush({ data: { id: 'style-1', current_revision: 2 } });
  });

  it('creates and approves immutable measurement specification versions', () => {
    service.createMeasurementSpec('style-1', {
      size_system_code: 'ADULT-CM',
      size_sequence: ['S', 'M', 'L'],
      base_size_code: 'M',
      measurement_condition: 'Garment measured flat in relaxed condition.',
      change_reason: 'Initial size specification for technical development.',
      points: [{
        point_code: 'CHEST_CIRC',
        name: 'Chest circumference',
        measurement_method: 'Measure around the chest at underarm level.',
        unit_code: 'CM',
        is_required: true,
        values: [
          { size_code: 'S', nominal_value: 96, tolerance_minus: 1, tolerance_plus: 1 },
          { size_code: 'M', nominal_value: 100, tolerance_minus: 1, tolerance_plus: 1 },
          { size_code: 'L', nominal_value: 104, tolerance_minus: 1, tolerance_plus: 1 },
        ],
      }],
    }).subscribe((response) => expect(response.data.status).toBe('DRAFT'));

    const create = http.expectOne('/api/v1/styles/style-1/measurement-specs');
    expect(create.request.method).toBe('POST');
    create.flush({ data: { id: 'measurement-1', status: 'DRAFT' } });

    service.approveMeasurementSpec('style-1', 'measurement-1').subscribe((response) => {
      expect(response.data.status).toBe('APPROVED');
    });
    const approve = http.expectOne('/api/v1/styles/style-1/measurement-specs/measurement-1/approve');
    expect(approve.request.method).toBe('POST');
    approve.flush({ data: { id: 'measurement-1', status: 'APPROVED' } });

    service.listMeasurementSpecs('style-1').subscribe((response) => {
      expect(response.data[0]?.size_sequence).toEqual(['S', 'M', 'L']);
      expect(response.data[0]?.points[0]?.values).toHaveLength(3);
    });
    const list = http.expectOne('/api/v1/styles/style-1/measurement-specs');
    expect(list.request.method).toBe('GET');
    list.flush({ data: [{ id: 'measurement-1', status: 'APPROVED', size_sequence: ['S', 'M', 'L'], points: [{ values: [{ size_code: 'S' }, { size_code: 'M' }, { size_code: 'L' }] }] }] });
  });

  it('creates versioned pattern sets, appends revisions and approves the exact revision', () => {
    const payload: PatternRevisionInput = {
      measurement_spec_version_id: 'measurement-1',
      pieces: [{ piece_code: 'FRONT', description: 'Front body panel', grain_direction: 'LENGTHWISE', seam_allowance: { side: 1 }, piece_count: 2 }],
      grade_rules: [{ point_code: 'CHEST_CIRC', from_size_code: 'S', to_size_code: 'M', increment: 4, unit_code: 'CM' }],
      change_summary: 'Initial pattern for approved measurement baseline.',
    };

    service.createPatternSet('style-1', {
      ...payload,
      pattern_code: 'OVERSHIRT-01',
      name: 'Main overshirt pattern',
    }).subscribe((response) => expect(response.revision.revision_no).toBe(1));
    const create = http.expectOne('/api/v1/styles/style-1/patterns');
    expect(create.request.method).toBe('POST');
    create.flush({ data: { id: 'pattern-1', pattern_code: 'OVERSHIRT-01' }, revision: { id: 'pattern-rev-1', revision_no: 1 } });

    service.createPatternRevision('pattern-1', payload).subscribe((response) => {
      expect(response.data.revision_no).toBe(2);
    });
    const revision = http.expectOne('/api/v1/patterns/pattern-1/revisions');
    expect(revision.request.method).toBe('POST');
    revision.flush({ data: { id: 'pattern-rev-2', revision_no: 2, status: 'DRAFT' } });

    service.approvePatternRevision('pattern-1', 'pattern-rev-2').subscribe((response) => {
      expect(response.data.status).toBe('APPROVED');
      expect(response.pattern_set.current_revision).toBe(2);
    });
    const approval = http.expectOne('/api/v1/patterns/pattern-1/revisions/pattern-rev-2/approve');
    expect(approval.request.method).toBe('POST');
    approval.flush({ data: { id: 'pattern-rev-2', revision_no: 2, status: 'APPROVED' }, pattern_set: { id: 'pattern-1', current_revision: 2 } });
  });


  it('creates sample requests and correction rounds through the controlled sample-room commands', () => {
    service.createSampleRequest('style-1', {
      pattern_set_id: 'pattern-1',
      pattern_revision_id: 'pattern-rev-2',
      sample_type: 'FIT',
      purpose: 'Validate the approved fit baseline before sample release.',
      requested_quantity: 1,
      requested_size_codes: ['M'],
      requested_colour_codes: ['NAVY'],
      required_by: '2027-03-20',
    }).subscribe((response) => {
      expect(response.data.status).toBe('REQUESTED');
      expect(response.data.pattern_revision_id).toBe('pattern-rev-2');
      expect(response.round.round_no).toBe(1);
    });

    const create = http.expectOne('/api/v1/styles/style-1/sample-requests');
    expect(create.request.method).toBe('POST');
    create.flush({
      data: { id: 'sample-1', status: 'REQUESTED', pattern_revision_id: 'pattern-rev-2' },
      round: { id: 'round-1', round_no: 1, status: 'REQUESTED' },
    });

    service.createSampleRound('sample-1', {
      round_reason: 'Correct chest balance after the first fit review.',
      planned_quantity: 1,
    }).subscribe((response) => expect(response.round.round_no).toBe(2));
    const round = http.expectOne('/api/v1/sample-requests/sample-1/rounds');
    expect(round.request.method).toBe('POST');
    round.flush({ data: { id: 'sample-1', status: 'REQUESTED' }, round: { id: 'round-2', round_no: 2 } });
  });

  it('records a fit session, measurement observations and maker-checker disposition', () => {
    service.createFitSession('round-1', {
      fit_size_code: 'M',
      fit_model_code: 'FIT-M-01',
      posture_code: 'STANDING',
      condition_notes: 'Garment worn over a light base layer after pressing.',
      session_date: '2027-03-10',
    }).subscribe((response) => expect(response.data.status).toBe('DRAFT'));
    const create = http.expectOne('/api/v1/sample-rounds/round-1/fit-sessions');
    expect(create.request.method).toBe('POST');
    create.flush({ data: { id: 'fit-1', status: 'DRAFT', fit_size_code: 'M' } });

    service.recordFitObservations('fit-1', [{
      point_code: 'CHEST_CIRC',
      observed_value: 100,
      issue_code: 'NONE',
      severity: 'NONE',
      finding: null,
      corrective_action: null,
    }]).subscribe((response) => expect(response.data[0]?.within_tolerance).toBe(true));
    const observations = http.expectOne('/api/v1/fit-sessions/fit-1/observations');
    expect(observations.request.body.observations[0].point_code).toBe('CHEST_CIRC');
    observations.flush({ data: [{ id: 'obs-1', within_tolerance: true }] });

    service.submitFitSession('fit-1').subscribe((response) => expect(response.data.status).toBe('SUBMITTED_FOR_REVIEW'));
    const submit = http.expectOne('/api/v1/fit-sessions/fit-1/submit');
    expect(submit.request.method).toBe('POST');
    submit.flush({ data: { id: 'fit-1', status: 'SUBMITTED_FOR_REVIEW' } });

    service.decideFitSession('fit-1', {
      decision: 'APPROVE',
      reason: 'All required measurement points are within the approved size tolerance.',
      actions: [],
    }).subscribe((response) => expect(response.sample_request.status).toBe('FIT_APPROVED'));
    const decide = http.expectOne('/api/v1/fit-sessions/fit-1/decisions');
    expect(decide.request.method).toBe('POST');
    decide.flush({ data: { id: 'fit-1', status: 'DECIDED' }, sample_request: { id: 'sample-1', status: 'FIT_APPROVED' } });
  });

});
